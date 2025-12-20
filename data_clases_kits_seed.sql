-- ============================================
-- Clase de Ciencia - Seed Data
-- Populates core taxonomies, clases, kits, items, guides, IA config
-- Compatible with schema: schema_clases_kits_full.sql + schema_clases_kits_ia.sql
-- Date: 2025-12-19
-- ============================================

SET NAMES utf8mb4;
USE `u626603208_clasedeciencia`;

-- =============================
-- 1) Taxonomies
-- =============================
INSERT INTO areas (nombre, slug) VALUES
  ('Física','fisica'),
  ('Química','quimica'),
  ('Biología','biologia'),
  ('Tecnología','tecnologia'),
  ('Ambiental','ambiental')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

INSERT INTO competencias (codigo, nombre) VALUES
  ('indagacion','Formulo preguntas, observo, registro datos'),
  ('explicacion','Establezco relaciones causales, modelo fenómenos'),
  ('uso_conocimiento','Aplico conceptos a situaciones reales')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

INSERT INTO categorias_items (nombre, slug) VALUES
  ('Eléctricos','electricos'),
  ('Magnéticos','magneticos'),
  ('Biología','biologia'),
  ('Química','quimica'),
  ('Tecnología','tecnologia'),
  ('Herramientas','herramientas'),
  ('Seguridad','seguridad')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- =============================
-- 2) Kit Items catalog (unique names)
-- =============================
INSERT INTO kit_items (nombre_comun, categoria_id, advertencias_seguridad, unidad, sku) VALUES
  ('Lente plástico 10x', (SELECT id FROM categorias_items WHERE slug='biologia'), '⚠️ Frágil, manipular con cuidado', 'pcs', 'BIO-LEN-10X'),
  ('Cartón rígido', (SELECT id FROM categorias_items WHERE slug='tecnologia'), NULL, 'pcs', 'TEC-CAR-RIG'),
  ('Banda elástica', (SELECT id FROM categorias_items WHERE slug='tecnologia'), NULL, 'pcs', 'TEC-BAN-ELA'),
  ('Globo de látex', (SELECT id FROM categorias_items WHERE slug='biologia'), '⚠️ Riesgo de asfixia, no apto <8 años', 'pcs', 'BIO-GLO-LAT'),
  ('Botella plástica 500ml', (SELECT id FROM categorias_items WHERE slug='tecnologia'), NULL, 'pcs', 'TEC-BOT-500'),
  ('Bomba de aire manual', (SELECT id FROM categorias_items WHERE slug='herramientas'), NULL, 'pcs', 'HER-BOM-AIR'),
  ('Pila AA', (SELECT id FROM categorias_items WHERE slug='electricos'), '⚠️ No cortocircuitar', 'pcs', 'ELE-PIL-AA'),
  ('Porta baterías AA', (SELECT id FROM categorias_items WHERE slug='electricos'), NULL, 'pcs', 'ELE-POR-AA'),
  ('Cable conductor', (SELECT id FROM categorias_items WHERE slug='electricos'), NULL, 'm', 'ELE-CAB-CON'),
  ('Interruptor mini', (SELECT id FROM categorias_items WHERE slug='electricos'), NULL, 'pcs', 'ELE-INT-MIN'),
  ('Bombillo LED 3V', (SELECT id FROM categorias_items WHERE slug='electricos'), NULL, 'pcs', 'ELE-LED-3V'),
  ('Papel filtro', (SELECT id FROM categorias_items WHERE slug='quimica'), '⚠️ Material frágil', 'pcs', 'QUI-PAP-FIL'),
  ('Embudo plástico', (SELECT id FROM categorias_items WHERE slug='quimica'), NULL, 'pcs', 'QUI-EMB-PLA'),
  ('Vaso precipitado plástico', (SELECT id FROM categorias_items WHERE slug='quimica'), NULL, 'pcs', 'QUI-VAS-PLA'),
  ('Tiras de pH', (SELECT id FROM categorias_items WHERE slug='quimica'), NULL, 'pcs', 'QUI-TIR-PH'),
  ('Diode germanio', (SELECT id FROM categorias_items WHERE slug='electricos'), NULL, 'pcs', 'ELE-DIO-GER'),
  ('Auricular cristal', (SELECT id FROM categorias_items WHERE slug='electricos'), NULL, 'pcs', 'ELE-AUR-CRI'),
  ('Alambre esmaltado 28AWG', (SELECT id FROM categorias_items WHERE slug='electricos'), NULL, 'm', 'ELE-ALM-28'),
  ('Imán neodimio', (SELECT id FROM categorias_items WHERE slug='magneticos'), '⚠️ Mantener lejos de dispositivos', 'pcs', 'MAG-IMA-NEO'),
  ('Clavo de hierro', (SELECT id FROM categorias_items WHERE slug='magneticos'), NULL, 'pcs', 'MAG-CLA-HIE'),
  ('Trampa de ratón', (SELECT id FROM categorias_items WHERE slug='tecnologia'), '⚠️ Riesgo de pellizco', 'pcs', 'TEC-TRA-RAT'),
  ('Rueda plástica 50mm', (SELECT id FROM categorias_items WHERE slug='tecnologia'), NULL, 'pcs', 'TEC-RUE-50'),
  ('Eje metálico', (SELECT id FROM categorias_items WHERE slug='tecnologia'), NULL, 'pcs', 'TEC-EJE-MET'),
  ('Motor DC 3-6V', (SELECT id FROM categorias_items WHERE slug='electricos'), NULL, 'pcs', 'ELE-MOT-DC'),
  ('Manivela plástica', (SELECT id FROM categorias_items WHERE slug='tecnologia'), NULL, 'pcs', 'TEC-MAN-PLA'),
  ('Panel solar 5V', (SELECT id FROM categorias_items WHERE slug='tecnologia'), NULL, 'pcs', 'TEC-PAN-5V'),
  ('Hélice plástica', (SELECT id FROM categorias_items WHERE slug='tecnologia'), NULL, 'pcs', 'TEC-HEL-PLA'),
  ('Carbón activado', (SELECT id FROM categorias_items WHERE slug='ambiental'), NULL, 'g', 'AMB-CAR-ACT'),
  ('Arena fina', (SELECT id FROM categorias_items WHERE slug='ambiental'), NULL, 'g', 'AMB-ARE-FIN'),
  ('Grava', (SELECT id FROM categorias_items WHERE slug='ambiental'), NULL, 'g', 'AMB-GRA-STD'),
  ('Sal de mesa', (SELECT id FROM categorias_items WHERE slug='quimica'), NULL, 'g', 'QUI-SAL-MES'),
  ('Rodaja de papa', (SELECT id FROM categorias_items WHERE slug='biologia'), NULL, 'pcs', 'BIO-ROD-PAP')
ON DUPLICATE KEY UPDATE sku=VALUES(sku);

-- =============================
-- 3) Clases (15 proyectos del portafolio)
-- =============================
-- Ciclo 1: Exploración (6°-7°)
INSERT INTO clases (nombre, slug, ciclo, grados, dificultad, duracion_minutos, resumen, objetivo_aprendizaje, seguridad, activo, destacado, status, published_at)
VALUES
  ('Microscopio sencillo','microscopio-sencillo',1, JSON_ARRAY(6,7), 'facil', 60,
   'Construye un microscopio artesanal para observar detalles invisibles.','Reconocer el uso de lentes para aumentar imágenes y describir observaciones científicas.',
   JSON_OBJECT('edad_min',11,'edad_max',13,'notas','⚠️ Manipular lentes y objetos pequeños con cuidado'), 1, 0, 'published', NOW()),
  ('Pulmón mecánico','pulmon-mecanico',1, JSON_ARRAY(6,7), 'facil', 60,
   'Modelo funcional de los pulmones usando presión de aire y movimiento.','Explicar la relación entre presión y volumen en un sistema respiratorio sencillo.',
   JSON_OBJECT('edad_min',11,'edad_max',13,'notas','⚠️ Supervisar uso de globos'), 1, 0, 'published', NOW()),
  ('Circuito eléctrico básico','circuito-electrico-basico',1, JSON_ARRAY(6,7), 'facil', 60,
   'Arma un circuito simple con batería, interruptor y LED.','Identificar componentes eléctricos básicos y observar transformaciones de energía.',
   JSON_OBJECT('edad_min',11,'edad_max',13,'notas','⚠️ No cortocircuitar baterías'), 1, 1, 'published', NOW()),
  ('Separación de mezclas','separacion-de-mezclas',1, JSON_ARRAY(6,7), 'facil', 60,
   'Aplica métodos físicos para separar mezclas cotidianas.','Clasificar mezclas y aplicar filtración y decantación de manera segura.',
   JSON_OBJECT('edad_min',11,'edad_max',13,'notas','⚠️ Manejo cuidadoso de agua y utensilios'), 1, 0, 'published', NOW()),
  ('Test de pH','test-de-ph',1, JSON_ARRAY(6,7), 'facil', 45,
   'Usa tiras de pH para identificar ácidos y bases.','Reconocer propiedades químicas y aplicar normas de seguridad en el laboratorio escolar.',
   JSON_OBJECT('edad_min',11,'edad_max',13,'notas','⚠️ No ingerir sustancias'), 1, 0, 'published', NOW());

-- Ciclo 2: Experimentación (8°-9°)
INSERT INTO clases (nombre, slug, ciclo, grados, dificultad, duracion_minutos, resumen, objetivo_aprendizaje, seguridad, activo, destacado, status, published_at)
VALUES
  ('Radio de cristal','radio-de-cristal',2, JSON_ARRAY(8,9), 'media', 90,
   'Construye un receptor de radio sin batería usando un diodo y bobina.','Explicar la propagación de ondas y la conversión de energía en comunicación.',
   JSON_OBJECT('edad_min',13,'edad_max',15,'notas','⚠️ Manipular alambres y componentes con cuidado'), 1, 1, 'published', NOW()),
  ('Motor eléctrico simple','motor-electrico-simple',2, JSON_ARRAY(8,9), 'media', 90,
   'Arma un motor básico que convierte energía eléctrica en movimiento.','Relacionar electricidad y magnetismo y analizar variables que afectan el movimiento.',
   JSON_OBJECT('edad_min',13,'edad_max',15,'notas','⚠️ Imán potente, evitar acercar a dispositivos'), 1, 1, 'published', NOW()),
  ('Osmosis con vegetales','osmosis-con-vegetales',2, JSON_ARRAY(8,9), 'media', 60,
   'Observa cambios por transporte celular en vegetales con soluciones salinas.','Explicar procesos celulares usando evidencia experimental.',
   JSON_OBJECT('edad_min',13,'edad_max',15,'notas','⚠️ Higiene y manejo de alimentos'), 1, 0, 'published', NOW()),
  ('Carro trampa de ratón','carro-trampa-de-raton',2, JSON_ARRAY(8,9), 'media', 90,
   'Construye un carro impulsado por energía potencial de una trampa.','Analizar fuerzas, fricción y transformación de energías en sistemas mecánicos.',
   JSON_OBJECT('edad_min',13,'edad_max',15,'notas','⚠️ Riesgo de pellizco, usar bajo supervisión'), 1, 0, 'published', NOW()),
  ('Generador manual (dinamo)','generador-manual-dinamo',2, JSON_ARRAY(8,9), 'media', 90,
   'Genera electricidad manualmente mediante inducción electromagnética.','Explicar generación eléctrica relacionando movimiento y energía.',
   JSON_OBJECT('edad_min',13,'edad_max',15,'notas','⚠️ Cuidado con conexiones eléctricas'), 1, 0, 'published', NOW());

-- Ciclo 3: Análisis (10°-11°)
INSERT INTO clases (nombre, slug, ciclo, grados, dificultad, duracion_minutos, resumen, objetivo_aprendizaje, seguridad, activo, destacado, status, published_at)
VALUES
  ('Carro solar','carro-solar',3, JSON_ARRAY(10,11), 'dificil', 120,
   'Construye y evalúa un vehículo impulsado por energía solar.','Analizar eficiencia energética y sostenibilidad en sistemas tecnológicos.',
   JSON_OBJECT('edad_min',15,'edad_max',18,'notas','⚠️ Panel frágil, manipulación cuidadosa'), 1, 1, 'published', NOW()),
  ('Turbina eólica de mesa','turbina-eolica-de-mesa',3, JSON_ARRAY(10,11), 'dificil', 120,
   'Diseña una turbina de mesa para convertir energía del viento.','Evaluar fuentes alternativas y analizar impacto tecnológico.',
   JSON_OBJECT('edad_min',15,'edad_max',18,'notas','⚠️ Hélice en movimiento, mantener distancia'), 1, 0, 'published', NOW()),
  ('Electroimán','electroiman',3, JSON_ARRAY(10,11), 'dificil', 90,
   'Construye un electroimán y analiza variables de fuerza y campo.','Analizar relación corriente-campo y formular explicaciones causales.',
   JSON_OBJECT('edad_min',15,'edad_max',18,'notas','⚠️ Calentamiento por corriente, usar brevemente'), 1, 1, 'published', NOW()),
  ('Tratamiento de agua','tratamiento-de-agua',3, JSON_ARRAY(10,11), 'dificil', 120,
   'Implementa un filtro de agua con capas y evalúa calidad.','Explicar procesos físico-químicos y relacionar ciencia con el entorno.',
   JSON_OBJECT('edad_min',15,'edad_max',18,'notas','⚠️ Uso responsable de reactivos y desecho'), 1, 0, 'published', NOW()),
  ('Análisis químico del entorno','analisis-quimico-del-entorno',3, JSON_ARRAY(10,11), 'dificil', 120,
   'Realiza pruebas químicas seguras a sustancias cotidianas.','Explicar transformaciones químicas con principios de seguridad y ética.',
   JSON_OBJECT('edad_min',15,'edad_max',18,'notas','⚠️ No ingerir sustancias, guantes recomendados'), 1, 0, 'published', NOW());

-- =============================
-- 4) Clase ↔ Áreas
-- =============================
INSERT INTO clase_areas (clase_id, area_id)
SELECT c.id, a.id FROM clases c JOIN areas a ON a.slug='biologia' WHERE c.slug IN ('microscopio-sencillo','pulmon-mecanico','osmosis-con-vegetales');
INSERT INTO clase_areas (clase_id, area_id)
SELECT c.id, a.id FROM clases c JOIN areas a ON a.slug='fisica' WHERE c.slug IN ('circuito-electrico-basico','radio-de-cristal','motor-electrico-simple','carro-trampa-de-raton','generador-manual-dinamo','carro-solar','turbina-eolica-de-mesa','electroiman');
INSERT INTO clase_areas (clase_id, area_id)
SELECT c.id, a.id FROM clases c JOIN areas a ON a.slug='quimica' WHERE c.slug IN ('separacion-de-mezclas','test-de-ph','tratamiento-de-agua','analisis-quimico-del-entorno');
INSERT INTO clase_areas (clase_id, area_id)
SELECT c.id, a.id FROM clases c JOIN areas a ON a.slug='tecnologia' WHERE c.slug IN ('carro-trampa-de-raton','carro-solar','turbina-eolica-de-mesa','radio-de-cristal');
INSERT INTO clase_areas (clase_id, area_id)
SELECT c.id, a.id FROM clases c JOIN areas a ON a.slug='ambiental' WHERE c.slug IN ('tratamiento-de-agua');

-- =============================
-- 5) Clase ↔ Competencias MEN
-- =============================
-- Ciclo 1: indagacion
INSERT INTO clase_competencias (clase_id, competencia_id)
SELECT c.id, comp.id FROM clases c JOIN competencias comp ON comp.codigo='indagacion'
WHERE c.slug IN ('microscopio-sencillo','pulmon-mecanico','circuito-electrico-basico','separacion-de-mezclas','test-de-ph');
-- Ciclo 2: explicacion
INSERT INTO clase_competencias (clase_id, competencia_id)
SELECT c.id, comp.id FROM clases c JOIN competencias comp ON comp.codigo='explicacion'
WHERE c.slug IN ('radio-de-cristal','motor-electrico-simple','osmosis-con-vegetales','carro-trampa-de-raton','generador-manual-dinamo');
-- Ciclo 3: uso_conocimiento + explicacion
INSERT INTO clase_competencias (clase_id, competencia_id)
SELECT c.id, comp.id FROM clases c JOIN competencias comp ON comp.codigo='uso_conocimiento'
WHERE c.slug IN ('carro-solar','turbina-eolica-de-mesa','electroiman','tratamiento-de-agua','analisis-quimico-del-entorno');
INSERT INTO clase_competencias (clase_id, competencia_id)
SELECT c.id, comp.id FROM clases c JOIN competencias comp ON comp.codigo='explicacion'
WHERE c.slug IN ('electroiman','tratamiento-de-agua','analisis-quimico-del-entorno');

-- =============================
-- 6) Kits vinculados a clases
-- =============================
INSERT INTO kits (clase_id, nombre, codigo, version, activo)
SELECT id, CONCAT('Kit: ', nombre), CONCAT('KIT-', UPPER(REPLACE(slug,'-','_'))), '1.0', 1 FROM clases;

-- =============================
-- 7) Componentes de kits
-- =============================
-- Microscopio sencillo
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 2, 1, 'Lentes para aumento', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='BIO-LEN-10X'
WHERE c.slug='microscopio-sencillo';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Estructura', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='TEC-CAR-RIG'
WHERE c.slug='microscopio-sencillo';

-- Pulmón mecánico
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 2, 1, 'Pulmones', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='BIO-GLO-LAT'
WHERE c.slug='pulmon-mecanico';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Caja torácica', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='TEC-BOT-500'
WHERE c.slug='pulmon-mecanico';

-- Circuito eléctrico básico
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 2, 1, 'Energía', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-PIL-AA'
WHERE c.slug='circuito-electrico-basico';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Soporte', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-POR-AA'
WHERE c.slug='circuito-electrico-basico';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1.5, 1, 'Conexiones', 3
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-CAB-CON'
WHERE c.slug='circuito-electrico-basico';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Control', 4
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-INT-MIN'
WHERE c.slug='circuito-electrico-basico';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Salida', 5
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-LED-3V'
WHERE c.slug='circuito-electrico-basico';

-- Separación de mezclas
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 2, 1, 'Filtración', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='QUI-PAP-FIL'
WHERE c.slug='separacion-de-mezclas';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Embudo', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='QUI-EMB-PLA'
WHERE c.slug='separacion-de-mezclas';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Recipiente', 3
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='QUI-VAS-PLA'
WHERE c.slug='separacion-de-mezclas';

-- Test de pH
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 10, 1, 'Medición', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='QUI-TIR-PH'
WHERE c.slug='test-de-ph';

-- Radio de cristal
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Detector', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-DIO-GER'
WHERE c.slug='radio-de-cristal';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Audio', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-AUR-CRI'
WHERE c.slug='radio-de-cristal';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 5, 1, 'Bobina', 3
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-ALM-28'
WHERE c.slug='radio-de-cristal';

-- Motor eléctrico simple
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 2, 1, 'Campo magnético', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='MAG-IMA-NEO'
WHERE c.slug='motor-electrico-simple';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Núcleo', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='MAG-CLA-HIE'
WHERE c.slug='motor-electrico-simple';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 2, 1, 'Bobina', 3
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-ALM-28'
WHERE c.slug='motor-electrico-simple';

-- Osmosis con vegetales
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 50, 1, 'Solución salina', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='QUI-SAL-MES'
WHERE c.slug='osmosis-con-vegetales';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 2, 1, 'Muestras vegetales', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='BIO-ROD-PAP'
WHERE c.slug='osmosis-con-vegetales';

-- Carro trampa de ratón
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Fuente de energía potencial', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='TEC-TRA-RAT'
WHERE c.slug='carro-trampa-de-raton';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 4, 1, 'Movimiento', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='TEC-RUE-50'
WHERE c.slug='carro-trampa-de-raton';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 2, 1, 'Transmisión', 3
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='TEC-EJE-MET'
WHERE c.slug='carro-trampa-de-raton';

-- Generador manual (dinamo)
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Generación', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-MOT-DC'
WHERE c.slug='generador-manual-dinamo';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Manivela', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='TEC-MAN-PLA'
WHERE c.slug='generador-manual-dinamo';

-- Carro solar
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Fuente solar', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='TEC-PAN-5V'
WHERE c.slug='carro-solar';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Tracción', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-MOT-DC'
WHERE c.slug='carro-solar';

-- Turbina eólica de mesa
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Captura de viento', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='TEC-HEL-PLA'
WHERE c.slug='turbina-eolica-de-mesa';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Generación', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-MOT-DC'
WHERE c.slug='turbina-eolica-de-mesa';

-- Electroimán
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 2, 1, 'Bobina', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='ELE-ALM-28'
WHERE c.slug='electroiman';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 1, 1, 'Núcleo', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='MAG-CLA-HIE'
WHERE c.slug='electroiman';

-- Tratamiento de agua
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 50, 1, 'Purificación', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='AMB-CAR-ACT'
WHERE c.slug='tratamiento-de-agua';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 200, 1, 'Filtración', 2
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='AMB-ARE-FIN'
WHERE c.slug='tratamiento-de-agua';
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 200, 1, 'Capa inferior', 3
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='AMB-GRA-STD'
WHERE c.slug='tratamiento-de-agua';

-- Análisis químico del entorno
INSERT INTO kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)
SELECT k.id, i.id, 10, 1, 'Indicador seguro', 1
FROM kits k JOIN clases c ON k.clase_id=c.id JOIN kit_items i ON i.sku='QUI-TIR-PH'
WHERE c.slug='analisis-quimico-del-entorno';

-- =============================
-- 8) Guías (pasos JSON) y recursos
-- =============================
INSERT INTO guias (clase_id, pasos, explicacion_cientifica)
SELECT c.id,
       JSON_ARRAY(
         JSON_OBJECT('titulo','Preparación','detalle','Revisa materiales y normas de seguridad.'),
         JSON_OBJECT('titulo','Construcción','detalle','Sigue la guía para armar el sistema.'),
         JSON_OBJECT('titulo','Observación','detalle','Registra resultados y comportamientos.'),
         JSON_OBJECT('titulo','Análisis','detalle','Responde preguntas guiadas y explica el fenómeno.')
       ),
       'Relación directa con los conceptos clave del portafolio.'
FROM clases c;

INSERT INTO recursos_multimedia (clase_id, tipo, url, titulo, descripcion, sort_order)
SELECT c.id, 'link', CONCAT('https://clasedeciencia.com/clase/', c.slug), 'Guía interactiva', 'Accede a la guía digital de la clase', 1
FROM clases c;

-- =============================
-- 9) IA Config + Prompts por Clase
-- =============================
INSERT INTO configuracion_ia (clave, valor, tipo, descripcion) VALUES
  ('palabras_peligro', JSON_ARRAY('fuego','explosión','ácido fuerte','cortocircuito','veneno'), 'json', 'Palabras que activan guardrails de seguridad')
ON DUPLICATE KEY UPDATE valor=VALUES(valor);

INSERT INTO prompts_clase (clase_id, prompt_contexto, conocimientos_previos, enfoque_pedagogico, preguntas_frecuentes, activo)
SELECT c.id,
       CONCAT('Contexto IA para la clase: ', c.nombre, '. Conceptos clave y seguridad según guía.'),
       JSON_ARRAY('Normas básicas de laboratorio','Mediciones y observación','Seguridad eléctrica/química según aplique'),
       'Guiar con preguntas abiertas, reforzar competencias MEN según ciclo.',
       JSON_ARRAY('¿Qué variable afecta más el resultado?','¿Cómo mejora la eficiencia?','¿Qué relación hay entre concepto y observación?'),
       1
FROM clases c
ON DUPLICATE KEY UPDATE prompt_contexto=VALUES(prompt_contexto);

-- =============================
-- FIN SEED
