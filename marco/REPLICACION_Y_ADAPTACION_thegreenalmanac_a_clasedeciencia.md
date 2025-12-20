# Guía de Replicación y Adaptación

Proyecto destino: clasedeciencia.com  
Origen base: base_paginas/thegreenalmanac.com  
Fecha: 19 de diciembre de 2025

Objetivo: documentar, paso a paso, cómo replicar la estructura funcional de The Green Almanac (TGA) y adaptarla al esquema y requerimientos de Clase de Ciencia (CdC) con la nueva base de datos `u626603208_clasedeciencia`.

---

## 1. Principios de Adaptación
- Mantener arquitectura PHP + PDO + includes, mobile-first y SEO.  
- Queries: siempre prepared statements con `?`.  
- Sin ejecución ni alteración de DB por el agente.  
- Debug en navegador con `console.log()` (🔍, ✅, ❌, ⚠️).  
- Escapar toda salida HTML con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.

Referencias clave:
- Esquema y relaciones: ver [marco/BASE_DE_DATOS_u626603208_clasedeciencia.md](marco/BASE_DE_DATOS_u626603208_clasedeciencia.md)
- Especificaciones generales: ver [marco/ANALISIS_Y_PLAN_CLASEDECIENCIA.md](marco/ANALISIS_Y_PLAN_CLASEDECIENCIA.md)

---

## 2. Mapa de Directorios (TGA → CdC)

Origen: [base_paginas/thegreenalmanac.com](base_paginas/thegreenalmanac.com)
Destino: raíz del proyecto CdC

- Copiar y adaptar:
  - `config.php` → credenciales y constantes CdC.
  - `includes/` → `header.php`, `footer.php`, helpers; agregar `db-functions.php` para CdC.
  - `assets/` → CSS/JS base; sumar `assets/js/catalogo-filtros.js`, `assets/js/asistente-ia.js`, `assets/js/analytics.js`.
  - `admin/` → módulos base; renombrar y adaptar a `proyectos`, `guias`, `materiales`; añadir `contratos/`, `entregas/`, `analytics/`.
  - Páginas públicas: `index.php`, `library.php`, `article.php`, `material.php`, `materials.php`, `search.php`, `section.php`, `sitemap.xml.php` → ver mapeo en sección 3.

---

## 3. Páginas Públicas — Mapeo y Cambios

### 3.1 `index.php` (TGA) → `index.php` (CdC)
- Sustituir origen de destacados: `articles` → `proyectos` (`destacado=1`, `activo=1`).
- Fichas muestran: ciclo, áreas, dificultad, duración, resumen.
- Helpers: `get_proyectos_destacados($pdo, $limit=6)`.

Ejemplo de query (PDO):
```php
$stmt = $pdo->prepare("SELECT id, nombre, slug, ciclo, duracion_minutos, dificultad, resumen FROM proyectos WHERE activo = 1 AND destacado = 1 ORDER BY orden_popularidad DESC, updated_at DESC LIMIT ?");
$stmt->execute([$limit]);
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### 3.2 `library.php` (TGA) → `catalogo.php` (CdC)
- Filtros en CdC:
  - `ciclo` (1,2,3), `areas` (via `proyecto_areas`), `dificultad`, `duracion_minutos` (rangos), búsqueda texto (`nombre`, `resumen`).
- JOINs: `proyectos` + `proyecto_areas` + `areas` (si hay filtro por área).

Ejemplo (búsqueda + filtros):
```php
$where = ["p.activo = 1"]; $params = []; $joins = [];
if (!empty($f['ciclo'])) { $where[] = "p.ciclo IN (".str_repeat('?,', count($f['ciclo'])-1).'?' . ")"; $params = array_merge($params,$f['ciclo']); }
if (!empty($f['areas'])) { $joins[] = "INNER JOIN proyecto_areas pa ON pa.proyecto_id = p.id"; $joins[] = "INNER JOIN areas a ON a.id = pa.area_id"; $where[] = "a.slug IN (".str_repeat('?,', count($f['areas'])-1).'?' . ")"; $params = array_merge($params,$f['areas']); }
if (!empty($f['dificultad'])) { $where[] = "p.dificultad IN (".str_repeat('?,', count($f['dificultad'])-1).'?' . ")"; $params = array_merge($params,$f['dificultad']); }
if (!empty($f['busqueda'])) { $where[] = "(p.nombre LIKE ? OR p.resumen LIKE ?)"; $params[] = '%'.$f['busqueda'].'%'; $params[] = '%'.$f['busqueda'].'%'; }
$sql = "SELECT DISTINCT p.* FROM proyectos p " . implode(' ',$joins) . " WHERE " . implode(' AND ',$where) . " ORDER BY p.orden_popularidad DESC, p.nombre ASC";
```

### 3.3 `article.php` (TGA) → `proyecto.php?slug=` (CdC)
- Cargar `proyectos` por `slug`.
- Cargar guía activa desde `guias` (`activa=1`).
- Cargar multimedia desde `recursos_multimedia`.
- Mostrar badges: ciclo, grados, áreas, dificultad, duración.
- Sidebar: Asistente IA (widget) + relacionados.

Helpers CdC:
- `get_proyecto_por_slug($pdo, $slug)`
- `get_guia_activa($pdo, $proyecto_id)`
- `get_recursos_multimedia($pdo, $proyecto_id)`
- `get_areas_por_proyecto($pdo, $proyecto_id)`

### 3.4 `material.php` y `materials.php`
- Fuente: `materiales` + `categorias_materiales`.
- Listado y ficha con seguridad (`advertencias_seguridad`, `manejo_recomendado`).

### 3.5 `search.php` → `buscar.php`
- Endpoint JSON en `api/buscar.php` con prepared statements.
- Campos: `proyectos.nombre`, `proyectos.resumen`; filtro opcional por `areas`.

### 3.6 `section.php`
- En CdC no hay tabla `sections`; usar `ciclo` y `grados` del proyecto.
- Vista por ciclo: listar `proyectos` donde `ciclo = ?`.

### 3.7 `sitemap.xml.php`
- Generar URLs para `index`, `catalogo`, `proyecto.php?slug=...`, `material.php?slug=...`.

---

## 4. Includes y Helpers (CdC)

### 4.1 `config.php`
- Mantener patrón de TGA; definir `SITE_URL` y conexión PDO.

### 4.2 `includes/db-functions.php`
Funciones sugeridas:
- `get_proyectos($pdo, array $filtros)`
- `get_proyecto_por_slug($pdo, $slug)`
- `get_guia_activa($pdo, $proyecto_id)`
- `get_recursos_multimedia($pdo, $proyecto_id)`
- `get_areas_por_proyecto($pdo, $proyecto_id)`
- `registrar_visita($pdo, $proyecto_id, $tipo_pagina, $url, $geo)`

### 4.3 `includes/functions.php`
- Utilidades de formato, badges, tiempo (minutos), sanitización.

---

## 5. Backend Admin — Mapeo

TGA → CdC:
- `admin/articles.php` → `admin/proyectos/index.php`
- `admin/article-edit.php` → `admin/proyectos/edit.php`
- `admin/materials.php` → `admin/materiales/index.php`
- `admin/sections.php` → reemplazado por edición de `ciclo` y `grados` en `proyectos`
- `admin/tags.php` → dividir en `areas` y `competencias`

Nuevos módulos CdC:
- `admin/guias/` (gestión de guía activa y versiones)
- `admin/contratos/` (CTeI)
- `admin/entregas/` (lotes y actas PDF)
- `admin/analytics/` (visitas e interacciones IA)

---

## 6. Integración del Asistente IA

### 6.1 Frontend
- Widget en `proyecto.php` con `assets/js/asistente-ia.js`.
- Enviar contexto mínimo: `proyecto_id`, nombre, materiales clave, conceptos, seguridad.

### 6.2 Backend (`api/ia-consulta.php`)
- Obtener contexto con `sp_obtener_contexto_proyecto(p_proyecto_id)` o vista `v_proyecto_contexto_ia`.
- Validar pregunta con `fn_es_pregunta_peligrosa()` → si `TRUE`, responder con `configuracion_ia.mensaje_guardrail`.
- Loggear evento en `ia_logs` y actualizar `ia_sesiones` / `ia_mensajes` con `sp_registrar_interaccion_ia`.

---

## 7. Analytics (Anónimo)
- `assets/js/analytics.js` captura eventos: visita, descarga PDF, consulta IA, clicks materiales.
- `api/analytics.php` almacena en `analytics_visitas` / `analytics_interacciones` con `sesion_hash`.

---

## 8. SEO y Accesibilidad
- Meta dinámicas por proyecto: `seo_title`, `seo_description`, canonical.
- Schema.org `HowTo` en `proyecto.php` (pasos, materiales).  
- Alt text, ARIA roles, navegación por teclado.

---

## 9. Checklist de Replicación
1) Copiar `includes/`, `assets/`, `admin/` y páginas base a raíz CdC.  
2) Configurar `config.php` con PDO y `SITE_URL`.  
3) Sustituir todas consultas `articles/sections/tags` por `proyectos` + relaciones (ver DB ref).  
4) Crear `includes/db-functions.php` con funciones CdC.  
5) Adaptar `library.php` → `catalogo.php` con filtros CdC.  
6) Adaptar `article.php` → `proyecto.php?slug=` con guía y multimedia.  
7) Adaptar `materials.php`/`material.php` a tablas CdC.  
8) Implementar `api/buscar.php` y `api/analytics.php`.  
9) Integrar IA (`api/ia-consulta.php`, widget JS).  
10) Revisar SEO y `sitemap.xml.php`.

---

## 10. Snippets de Debug
```javascript
console.log('🔍 [catalogo] filtros:', filtros);
console.log('✅ [catalogo] resultados:', proyectos.length);
console.log('❌ [catalogo] error query:', error.message);
console.log('⚠️ [ia] guardrail activado');
```

---

## 11. Riesgos y Mitigaciones
- Cambios de esquema: usar vistas `v_proyecto_contexto_ia` para simplificar JOINs.  
- Seguridad IA: mantener `palabras_peligro` actualizado en `configuracion_ia`.  
- Performance catálogo: paginación si la lista crece; índices en FK ya definidos.

---

## 12. Referencias
- DB: [marco/BASE_DE_DATOS_u626603208_clasedeciencia.md](marco/BASE_DE_DATOS_u626603208_clasedeciencia.md)  
- Plan: [marco/ANALISIS_Y_PLAN_CLASEDECIENCIA.md](marco/ANALISIS_Y_PLAN_CLASEDECIENCIA.md)  
- Código base: [base_paginas/thegreenalmanac.com](base_paginas/thegreenalmanac.com)
