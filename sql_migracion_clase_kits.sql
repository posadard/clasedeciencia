-- Migración: Relación N:M entre clases y kits
-- Fecha: 2025-12-20

-- 1. Crear tabla de relación clase_kits
CREATE TABLE `clase_kits` (
  `clase_id` int(11) NOT NULL,
  `kit_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `es_principal` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Kit principal de la clase',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`clase_id`, `kit_id`),
  KEY `idx_clase_kits_clase` (`clase_id`),
  KEY `idx_clase_kits_kit` (`kit_id`),
  KEY `idx_clase_kits_order` (`clase_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrar datos existentes de kits.clase_id a clase_kits
INSERT INTO `clase_kits` (`clase_id`, `kit_id`, `sort_order`, `es_principal`)
SELECT `clase_id`, `id`, 1, 1
FROM `kits`
WHERE `clase_id` IS NOT NULL;

-- 3. Agregar Foreign Keys
ALTER TABLE `clase_kits`
  ADD CONSTRAINT `fk_clase_kits_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clase_kits_kit` FOREIGN KEY (`kit_id`) REFERENCES `kits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 4. OPCIONAL: Eliminar columna clase_id de kits si quieres total N:M
-- Comenta esta línea si prefieres mantener clase_id por compatibilidad
-- ALTER TABLE `kits` DROP FOREIGN KEY `fk_kits_clase`;
-- ALTER TABLE `kits` DROP COLUMN `clase_id`;

-- 5. Verificar migración
SELECT 
    ck.clase_id,
    c.nombre AS clase_nombre,
    ck.kit_id,
    k.nombre AS kit_nombre,
    ck.es_principal
FROM clase_kits ck
JOIN clases c ON c.id = ck.clase_id
JOIN kits k ON k.id = ck.kit_id
ORDER BY ck.clase_id, ck.sort_order;

-- Resultado esperado: 15 filas (una por cada kit asignado)
