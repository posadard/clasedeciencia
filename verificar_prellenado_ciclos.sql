-- ============================================================
-- SCRIPT DE VERIFICACIÓN - PRELLENADO AUTOMÁTICO CICLOS
-- ============================================================
-- Verifica que los datos de ciclos estén correctos para
-- el prellenado automático de grados y edades
-- ============================================================

-- 1. Ver todos los ciclos con sus datos de grados y edades
SELECT 
    numero AS 'Ciclo',
    nombre AS 'Nombre',
    grados AS 'Grados JSON',
    grados_texto AS 'Grados Texto',
    edad_min AS 'Edad Min',
    edad_max AS 'Edad Max',
    activo AS 'Activo'
FROM ciclos 
ORDER BY numero;

-- 2. Ver ciclos activos (los que se mostrarán en el selector)
SELECT 
    numero,
    nombre,
    CONCAT('[', grados, ']') as grados_formateados,
    CONCAT(edad_min, ' - ', edad_max, ' años') as rango_edad
FROM ciclos 
WHERE activo = 1
ORDER BY numero;

-- 3. Verificar que grados sean arrays JSON válidos
SELECT 
    numero,
    nombre,
    grados,
    CASE 
        WHEN JSON_VALID(grados) THEN '✅ JSON Válido'
        ELSE '❌ JSON Inválido'
    END as validez_json
FROM ciclos;

-- 4. Ver clases actuales y sus ciclos para comparar
SELECT 
    c.id,
    c.nombre,
    c.ciclo,
    ci.nombre as ciclo_nombre,
    c.grados as grados_clase,
    ci.grados as grados_ciclo,
    JSON_EXTRACT(c.seguridad, '$.edad_min') as edad_min_clase,
    ci.edad_min as edad_min_ciclo,
    JSON_EXTRACT(c.seguridad, '$.edad_max') as edad_max_clase,
    ci.edad_max as edad_max_ciclo
FROM clases c
LEFT JOIN ciclos ci ON c.ciclo = ci.numero
ORDER BY c.ciclo, c.id
LIMIT 10;

-- ============================================================
-- RESULTADO ESPERADO
-- ============================================================
-- Los ciclos activos (1-5) deben tener:
--
-- Ciclo 1 (Cimentación):
--   - grados: [1, 2, 3]
--   - edad_min: 6
--   - edad_max: 8
--
-- Ciclo 2 (Consolidación):
--   - grados: [4, 5]
--   - edad_min: 9
--   - edad_max: 11
--
-- Ciclo 3 (Exploración):
--   - grados: [6, 7]
--   - edad_min: 12
--   - edad_max: 13
--
-- Ciclo 4 (Experimentación):
--   - grados: [8, 9]
--   - edad_min: 14
--   - edad_max: 15
--
-- Ciclo 5 (Análisis y Proyección):
--   - grados: [10, 11]
--   - edad_min: 16
--   - edad_max: 17
-- ============================================================

-- ============================================================
-- PRUEBA MANUAL EN NAVEGADOR
-- ============================================================
-- 1. Ir a /admin/clases/edit.php?id=6 (o nueva clase)
-- 2. Abrir DevTools Console (F12)
-- 3. Cambiar selector de Ciclo
-- 4. Verificar en consola:
--    - "🔍 [ClasesEdit] Ciclo seleccionado: Exploración" (u otro)
--    - "✅ [ClasesEdit] Grados prellenados: [6, 7]" (según ciclo)
--    - "✅ [ClasesEdit] Edad mínima: 12" (según ciclo)
--    - "✅ [ClasesEdit] Edad máxima: 13" (según ciclo)
-- 5. Verificar que:
--    - Los checkboxes correspondientes están marcados
--    - Campo "Edad mínima" tiene el valor correcto
--    - Campo "Edad máxima" tiene el valor correcto
--    - Los campos siguen siendo editables manualmente
--    - Se muestran los 11 checkboxes (1° a 11°) en grid responsivo
--
-- EJEMPLOS POR CICLO:
--   Ciclo 1: Marca 1°, 2°, 3° | Edad 6-8
--   Ciclo 2: Marca 4°, 5° | Edad 9-11
--   Ciclo 3: Marca 6°, 7° | Edad 12-13
--   Ciclo 4: Marca 8°, 9° | Edad 14-15
--   Ciclo 5: Marca 10°, 11° | Edad 16-17
-- ============================================================
