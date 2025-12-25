# Análisis Profundo: Sistema de Seguridad y Edad (Kits, Componentes y Manuales)

Fecha: 2025-12-24

## Objetivo
Revisar a fondo cómo se obtienen, editan, almacenan y renderizan las medidas de seguridad y rangos de edad en el sistema, tanto para:
- Kits (`kits.seguridad` JSON)
- Componentes (`kit_items.advertencias_seguridad` JSON)
- Manuales (`kit_manuals.seguridad_json` JSON), con ámbito `kit` y `componente`

Entender el flujo entre Admin (edición), BD, API, y Frontend (público), detectar inconsistencias y diseñar guía de corrección.

---

## Esquema de Base de Datos (u626603208_clasedeciencia.sql)

- `kits.seguridad`: `LONGTEXT` con `CHECK (json_valid(seguridad))`. Estructura observada:
  - `{"edad_min": 12, "edad_max": 18, "notas": "texto"}`
- `kit_items.advertencias_seguridad`: migrada a `LONGTEXT` JSON (se debe asegurar `utf8mb4_bin` y `CHECK json_valid`). Estructura objetivo (consistente):
  - `{"edad_min": 12, "edad_max": 18, "notas": "texto"}`
- `kit_manuals.seguridad_json`: JSON libre (estructura heterogénea historizada). Formatos vistos:
  - Estructurado nuevo: `{ "usar_seguridad_kit": true, "edad": { "min": 10, "max": 14 }, "notas_extra": [{"nota":"..","categoria":"eléctrico"}] }`
  - Variante antigua: `{ "edad": {"min":..,"max":..}, "notas": [...] }`
  - Array simple: `[{"nota":".."}]` o mezclas con `edad` dentro del primer elemento.
- Relaciones clave:
  - `kit_manuals.kit_id` → vínculo del manual al kit (ámbito kit)
  - `kit_manuals.item_id` → vínculo del manual al componente (ámbito componente)

Riesgo: heterogeneidad del JSON en `seguridad_json` requiere lógica robusta de parseo/merge.

---

## Admin: Edición de Seguridad

### 1) Editar Componentes ([admin/componentes/edit.php])
- Carga y parseo:
  - Lee `kit_items.advertencias_seguridad`, intenta `json_decode`; si falla queda vacío.
  - Prefill de UI:
    - `adv_edad_min`, `adv_edad_max`
    - `adv_notas`
    - `adv_json_raw` (editor avanzado que tiene prioridad)
- Guardado:
  - Si hay `adv_json_raw` válido: se guarda tal cual (prioridad)
  - Si no: construye JSON desde guiados (claves `edad_min`, `edad_max`, `notas`) o `NULL` si vacío.
- Resultado: `kit_items.advertencias_seguridad` queda estandarizado como JSON coherente con kits.

Observación: este flujo permite que el formulario sea la fuente de verdad (si el usuario cambia algo y aún no se refleja en BD, la próxima guardada lo actualiza). Bien alineado con la petición.

### 2) Editar Kits ([admin/kits/edit.php])
- Carga y guardado de `kits.seguridad` con claves `edad_min`, `edad_max`, `notas`.
- UI tiene campos específicos para edad mínima/máxima y notas.

### 3) Editar Manuales ([admin/kits/manuals/edit.php])
- Ámbito y entidad:
  - `ambito='kit'` → requiere `kit_id` (panel de seguridad del kit visible)
  - `ambito='componente'` → requiere `item_id`; `kit_id` puede ser `NULL`
- Panel Seguridad (UI Builder):
  - Para kits:
    - Renderiza panel "Medidas del kit" con edad y notas desde `kits.seguridad`
    - Checkbox: "Incluir seguridad del kit" → fusiona al guardar
  - Campos propios del manual:
    - Edad segura `min/max`
    - Lista de notas de seguridad (nota/categoria)
  - Serialización al enviar:
    - Con "usar kit": `{ usar_seguridad_kit:true, edad?:..., notas_extra?:[...] }`
    - Sin "usar kit": `{ edad:{min,max}, notas:[...] }` o sólo `notas:[...]` si no hay edad
- Prefill cuando `ambito='componente'`:
  - Server-side: crea `COMPONENT_SAFETY` leyendo `kit_items.advertencias_seguridad`
  - Client-side init: si el manual ya tiene `seguridad_json`, AHORA se mergea igualmente:
    - Rellena `sec-age-min/max` sólo si están vacíos
    - Agrega la `notas` del componente a la lista si no está

Riesgo/Matiz:
- Antes, el prefill de componente sólo corría si `seguridad_json` estaba vacío, por eso no se veía. Se corrigió a merge-incondicional (no sobreescribe datos ya presentes).
- El panel "Medidas del kit" se muestra/muta independientemente del ámbito. Para `ambito=componente`, el panel de kit permanece pero sin datos; esto puede confundir. Considerar mostrar un panel "Medidas del componente" para claridad (opcional).

---

## Frontend Público: Render de Seguridad

### 1) Página del Manual ([manual.php])
- Ambito y entidad:
  - Deriva kit vía `manual.kit_id` (no usando `item_id` cuando `ambito=componente`)
  - Deriva componente vía `manual.item_id`; si falta → fallback por slug derivado
- Bloque Seguridad:
  - Título: `⚠️ Seguridad del Componente` cuando `ambito=componente`; `⚠️ Seguridad` cuando `ambito=kit`
  - Conformación de seguridad efectiva:
    - Parse robusto de `manual.seguridad_json` (maneja formatos nuevos y antiguos)
    - `effectiveAge`: toma `edad.min/max` del manual; si faltan y `ambito=kit`, hereda de `kits.seguridad`
    - `manualNotes`: toma de `notas_extra` o `notas` según formato
    - `kitNotesText`: incluye texto libre de `kits.seguridad.notas` sólo si `usar_seguridad_kit=true` y `ambito=kit`
    - `hasAnySafety`: gate que decide render (verdadero si hay edad, notas, o `usar_seguridad_kit`)
  - Advertencias del componente:
    - Si `ambito=componente` y hay `comp.advertencias_seguridad` (cadena), se muestra inline dentro de Seguridad
    - Nota: si el componente guarda JSON y no cadena, la renderización hoy imprime la cadena cruda; conviene estandarizar lectura JSON para edad y notas
  - Concatenaciones extra (sólo `ambito=kit`):
    - Trunca y concatena advertencias por cada componente del kit y por herramientas del manual

Riesgo:
- Gate de renderización depende de `hasAnySafety` (manual y/o kit). Si el manual `seguridad_json` está vacío y no hay kit (ámbito componente), entonces sólo se mostrará el bloque si `comp.advertencias_seguridad` inline existe — y hoy se imprime aunque `hasAnySafety` sea falso, porque el bloque completo se condiciona por `hasAnySafety || discontinued`. Aquí está correcto: si no hay edad/notas, el bloque Seguridad puede no aparecer, pero la advertencia inline del componente sí está dentro del mismo bloque; por lo tanto, si `hasAnySafety` es falso, se oculta también la advertencia del componente (motivo por el que no se veía). Solución: permitir que la advertencia del componente haga que `hasAnySafety` sea verdadero (ver guía).

### 2) Página de Clase ([clase.php])
- Muestra kit y componentes, incluidas advertencias de componentes (`kit_items.advertencias_seguridad`) en listas.
- Usa edad y notas del kit (`kits.seguridad`).

### 3) Buscar ([buscar.php]) y `api/componentes-data.php`
- Referencian `kit_items.advertencias_seguridad` para descripción/listados.

---

## API

- `api/kit-get.php`: devuelve `kits.seguridad` en payload, usado por el editor de manuales (UI) para mostrar panel del kit.

No existe API dedicada para traer `kit_items.advertencias_seguridad` por `item_id` en el editor de manuales; se resolvió server-side embebiendo `COMPONENT_SAFETY`.

---

## Problemas Detectados

1) Prefill de seguridad del componente en `admin/kits/manuals/edit.php` no ocurría si el manual ya tenía `seguridad_json` → corregido a merge condicional (no sobreescribe, complementa).
2) Gate de render en público (`manual.php`) depende de edad/notas del manual o de usar seguridad de kit; las advertencias del componente inline están dentro del mismo bloque. Si el manual no aporta nada y no es kit, el bloque no aparece, ocultando la advertencia del componente.
3) Heterogeneidad del formato de `seguridad_json` en `kit_manuals` obliga a parseos defensivos; esto se maneja pero puede seguir causando sorpresas si se añaden nuevos formatos.
4) Desalineación visual: el panel "Medidas del kit" aparece (muted) incluso en ámbito componente dentro del editor de manuales; confunde. No bloqueante.

---

## Guía de Corrección (Plan)

1) Public (manual.php):
   - Ajustar `hasAnySafety` para considerar advertencia del componente como señal para mostrar el bloque de Seguridad cuando `ambito='componente'`.
     - Ej.: `if ($ambito==='componente' && $comp && !empty($comp['advertencias_seguridad'])) { $hasAnySafety = true; }`
   - Si `kit_items.advertencias_seguridad` es JSON, parsear y mostrar sus campos:
     - Mostrar edad del componente si existen `edad_min/edad_max` dentro del bloque.
     - Mostrar `notas` del componente como item de lista (categoria vacía).

2) Admin (manuals editor):
   - Mantener merge condicional ya aplicado para prefill desde `COMPONENT_SAFETY`.
   - Opcional: añadir panel "Medidas del componente" al UI cuando `ambito='componente'` para dar visibilidad y coherencia.
   - Opcional: cuando se cambie el `item_id` en el editor, realizar fetch dinámico (API o endpoint simple) para actualizar `COMPONENT_SAFETY` sin recargar.

3) Datos/Consistencia:
   - Confirmar que la migración de `kit_items.advertencias_seguridad` a JSON tiene `CHECK (json_valid(...))` y collations adecuados en la BD.
   - Estandarizar que `kit_items.advertencias_seguridad` use claves `edad_min`, `edad_max`, `notas` (igual que `kits.seguridad`) para facilitar reuso.

4) No romper formatos antiguos:
   - Mantener parseo defensivo en manuales para `seguridad_json` (ya implementado) y añadir parseo defensivo en componentes si `advertencias_seguridad` es texto plano.

---

## Verificaciones Prácticas (Checklist)

- Editor de componentes: guardar con edad/notas; reabrir y verificar prefill.
- Editor de manuales (ámbito componente): seleccionar un componente con `advertencias_seguridad` JSON y verificar que:
  - Edad se prellena si vacía.
  - Nota del componente se añade si no existe.
- Página pública del manual (ámbito componente):
  - Con manual sin `seguridad_json`, pero componente con `advertencias_seguridad`:
    - Debe mostrarse el bloque Seguridad con advertencia del componente y, si existen en el JSON del componente, edad del componente.
  - Con manual con `seguridad_json` + componente:
    - Debe aparecer tanto lo del manual como advertencia del componente.

---

## Conclusión
- El sistema ya integra las fuentes de seguridad/edad (kit, manual, componente). Las principales causas del síntoma de "no se ve la seguridad del componente" eran el gating del bloque de seguridad y la condición de prefill en el editor de manuales.
- Con los cambios en el editor (merge condicional) y el ajuste recomendado en `manual.php` (activar bloque Seguridad si hay advertencia del componente), el comportamiento quedará alineado con la expectativa: que lo que se ve en el formulario prevalezca y se refleje en el render público, incluso si la BD aún no tenía valores recientes.

> Siguiente paso: aplicar los ajustes mínimos en `manual.php` según esta guía.
