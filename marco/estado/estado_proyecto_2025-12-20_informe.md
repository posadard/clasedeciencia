# Estado del Proyecto – Clase de Ciencia (2025-12-20)

## Resumen Ejecutivo
- Catálogo y búsqueda unificados en la ruta pública `/clases`, con URL amigable de búsqueda `/clases/buscar/<termino>`.
- Limpieza de páginas duplicadas: se eliminaron `catalogo.php`, `search.php`, `library.php`, `section.php`, `article.php`. `.htaccess` redirige 301 a `/clases`.
- Mejoras de UX: tarjetas de ciclos clickeables en la homepage, advertencias de seguridad por componente en `proyecto.php`, remoción del campo "Código" visual del kit.
- Administración de kits: corregida la gestión de componentes en `admin/kits/edit.php` para alinear con el esquema real (`sort_order`, sin `id`), y se agregó soporte de `notas` al agregar y listar.

## Arquitectura y Rutas
- Canonical: `/clases` (catálogo + filtros + resultados). Canonical de búsqueda: `/clases/buscar/<termino>`.
- Reescrituras en `.htaccess`:
  - `/clases` → `clases.php`
  - `/clases/buscar/<termino>` → `clases.php?q=<termino>`
  - Redirecciones 301 para legacy: `catalogo.php`, `search.php`, `library.php`, `section.php`, `article.php` → `/clases`
  - Slugs dinámicos (`/{slug}`) se enrutan a `clases.php` para detectar ciclo/área y, de no coincidir, derivar al detalle de proyecto.

## Frontend Público
- `index.php`: tarjetas de exploración por ciclo (1,2,3) ahora son totalmente clickeables y accesibles por teclado; se añadieron logs de depuración.
- `proyecto.php`: se muestran `advertencias_seguridad` por cada `kit_item` dentro de componentes; se eliminó visualmente el "Código" del kit.
- `assets/js/home-search.js`: la acción Enter redirige a `/clases/buscar/<termino>`; el CTA "Ver catálogo" apunta a `/clases`.
- Canónicos y enlaces actualizados a la ruta unificada.

## Backend Admin (Kits)
- Archivo: `admin/kits/edit.php`.
- Problema detectado: el código asumía columnas `kc.id` y `kc.orden` en `kit_componentes`; el esquema correcto usa `sort_order` y no define `id` (clave compuesta `kit_id,item_id`).
- Correcciones:
  - SELECT: `kc.item_id, kc.cantidad, kc.sort_order AS orden, kc.notas` (join con `kit_items`).
  - INSERT: `kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)`.
  - DELETE: por `(kit_id, item_id)` usando `kc_item_id` en el formulario.
  - UI: nueva columna "Notas" en la tabla y campo de texto opcional para capturar `notas` al agregar.
- Logs de depuración presentes (✅, 🔍, ❌, ⚠️) según política.

## Base de Datos (tablas relevantes)
- `kits (id, clase_id, nombre, codigo, version, activo, created_at, updated_at)`
- `kit_items (id, nombre_comun, categoria_id, advertencias_seguridad, unidad, sku)`
- `kit_componentes (kit_id, item_id, cantidad, es_incluido_kit, notas, sort_order)`
- Vista `v_clase_kits_detalle`: une `kits`, `kit_componentes`, `kit_items` para reportes y consultas.
- Restricciones:
  - FK `kits.clase_id` → `clases.id`
  - FK `kit_componentes.item_id` → `kit_items.id`
  - FK `kit_componentes.kit_id` → `kits.id`
  - FK `kit_items.categoria_id` → `categorias_items.id`

## Seguridad y Normativas
- Consultas PDO con prepared statements (SQL injection mitigado).
- Escape de salida HTML con `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- CSRF en formularios admin (`auth.php`, tokens).
- Política de depuración vía `console.log()` con emojis: 🔍 (debug), ✅ (success), ❌ (error), ⚠️ (warning).
- Guardrails de IA presentes en `api/ia-consulta.php`.

## Pendientes y Próximos Pasos
- Ajustar enlaces de artículos relacionados en `material.php` que apuntan a `/article.php?slug=...`; proponer mapeo a `/proyecto.php?slug=...` si el slug coincide.
- DRY de normalización de búsqueda (acentos, expansión de keywords) entre `clases.php` y endpoints.
- UI Admin: agregar edición de `notas` y `es_incluido_kit` en filas existentes (inline edit o modal).
- CSS: limpieza de estilos residuales del "Código" del kit, si existieran.
- Documentación: actualizar referencias en `marco/*` para reflejar la ruta canónica `/clases`.

## Riesgos/Impactos
- Ruptura de enlaces externos legacy: mitigada con 301 a `/clases`.
- SEO: revisar `sitemap.xml.php` para asegurar cobertura de `/clases` y rutas amigables.

## Verificaciones Recomendadas (QA Manual)
1. Navegar `/clases` y aplicar filtros; revisar logs 🔍 en la consola.
2. Probar `/clases/buscar/energia` y confirmar resultados y canonical correcto.
3. Abrir `admin/kits/edit.php`, añadir y eliminar componentes (ver `notas`, `orden`); validar persistencia.
4. Revisar `proyecto.php` y confirmar visualización de advertencias por componente.

---
Última actualización: 2025-12-20
