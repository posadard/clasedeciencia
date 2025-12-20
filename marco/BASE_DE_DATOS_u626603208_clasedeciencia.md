# Referencia de Base de Datos — u626603208_clasedeciencia

Fecha: 19 de diciembre de 2025

Objetivo: Documentar propósito, campos, relaciones, vistas, procedimientos y funciones de la base de datos para adaptar el código de `base_paginas/thegreenalmanac.com` al proyecto `clasedeciencia.com`.

---

## Visión General

Dominios principales:
- Contenido educativo: `proyectos`, `guias`, `recursos_multimedia`
- Taxonomías: `areas`, `competencias`, `categorias_materiales`, `materiales`
- Relaciones de contenido: `proyecto_areas`, `proyecto_competencias`, `proyecto_materiales`
- IA asistente: `configuracion_ia`, `ia_sesiones`, `ia_mensajes`, `ia_logs`, `ia_respuestas_cache`, `prompts_proyecto`, `ia_stats_proyecto`
- Administrativo CTeI: `contratos`, `contrato_proyectos`, `justificacion_ctei`, `lotes_kits`, `entregas`, `entrega_lotes`
- Analítica (anónima): `analytics_visitas`, `analytics_interacciones`
- Vistas: `v_proyecto_contexto_ia`, `v_proyecto_materiales_detalle`
- Procedimientos: `sp_obtener_contexto_proyecto`, `sp_registrar_interaccion_ia`, `sp_limpiar_sesiones_antiguas`, `sp_buscar_respuesta_cache`
- Función: `fn_es_pregunta_peligrosa`

Nota: Todas las claves foráneas usan `ON DELETE CASCADE` cuando el vínculo es estrictamente dependiente del proyecto; en materiales se usa `ON DELETE SET NULL` para preservar integridad histórica.

---

## Tablas de Contenido Educativo

### `proyectos`
Entidad principal. Define el proyecto científico público.
- `id` INT PK
- `nombre` VARCHAR(255): título público
- `slug` VARCHAR(255) UNIQUE: URL amigable
- `ciclo` ENUM('1','2','3'): 1=6°-7°, 2=8°-9°, 3=10°-11°
- `grados` JSON: e.g. [6,7]
- `duracion_minutos` INT: estimado
- `dificultad` ENUM('facil','medio','dificil')
- `resumen` TEXT: descripción breve
- `objetivo_aprendizaje` TEXT
- `imagen_portada`, `video_portada` VARCHAR(255)
- `seguridad` JSON: {edad_min, requiere_supervision, advertencias[]}
- SEO: `seo_title`, `seo_description`, `canonical_url`
- Control: `activo` BOOL, `destacado` BOOL, `orden_popularidad` INT
- `created_at`, `updated_at` TIMESTAMP
Relaciones: con `guias`, `proyecto_areas`, `proyecto_competencias`, `proyecto_materiales`, `recursos_multimedia`.

### `guias`
Una guía activa por proyecto; versiones históricas permitidas.
- `id` INT PK
- `proyecto_id` INT FK → `proyectos(id)` ON DELETE CASCADE
- `version` VARCHAR(20)
- Secciones: `introduccion` TEXT, `materiales_kit` JSON, `materiales_adicionales` JSON, `seccion_seguridad` TEXT, `pasos` JSON, `explicacion_cientifica` TEXT, `conceptos_clave` JSON, `conexiones_realidad` TEXT, `para_profundizar` TEXT
- MEN: `competencias_men` JSON, `dba_relacionados` JSON, `estandares_men` JSON
- `activa` BOOL, `created_at` TIMESTAMP
Índices: `(proyecto_id, activa)`

### `recursos_multimedia`
Galería de apoyos del proyecto.
- `id` INT PK
- `proyecto_id` INT FK → `proyectos(id)` CASCADE
- `tipo` ENUM('imagen','video','simulacion','pdf')
- `titulo`, `descripcion` TEXT
- `url` VARCHAR(500)
- `orden` INT
Índices: `proyecto_id`

---

## Taxonomías y Materiales

### `areas`
- `id` INT PK
- `nombre` VARCHAR(100) (Física, Química, Biología, Tecnología, Ambiental)
- `slug` VARCHAR(100)
- `color` VARCHAR(7): código hex
- `descripcion` TEXT

### `competencias`
- `id` INT PK
- `nombre` VARCHAR(255)
- `descripcion` TEXT
- `tipo` ENUM('indagacion','explicacion','uso_conocimiento')

### `categorias_materiales`
- `id` INT PK
- `nombre`, `slug` VARCHAR(100)
- `icono` VARCHAR(50): emoji o clase CSS
- `descripcion` TEXT

### `materiales`
- `id` INT PK
- `nombre_comun`, `nombre_tecnico` VARCHAR(255)
- `descripcion` TEXT
- `slug` VARCHAR(255) UNIQUE
- `categoria_id` INT FK → `categorias_materiales(id)` ON DELETE SET NULL
- `imagen` VARCHAR(255)
- Seguridad: `advertencias_seguridad` TEXT, `manejo_recomendado` TEXT
- `created_at` TIMESTAMP

### `proyecto_areas`
- `proyecto_id` INT FK → `proyectos(id)` CASCADE
- `area_id` INT FK → `areas(id)` CASCADE
PK compuesta `(proyecto_id, area_id)`

### `proyecto_competencias`
- `proyecto_id` INT FK → `proyectos(id)` CASCADE
- `competencia_id` INT FK → `competencias(id)` CASCADE
PK compuesta `(proyecto_id, competencia_id)`

### `proyecto_materiales`
- `proyecto_id` INT FK → `proyectos(id)` CASCADE
- `material_id` INT FK → `materiales(id)` CASCADE
- `cantidad` VARCHAR(50)
- `es_incluido_kit` BOOL
- `notas` TEXT
PK compuesta `(proyecto_id, material_id)`

---

## Asistente de IA

### `configuracion_ia`
Parámetros de IA (Groq) y guardrails.
- `id` INT PK
- `clave` VARCHAR(100)
- `valor` TEXT
- `tipo` ENUM('texto','numero','booleano','json','secreto')
- `descripcion` TEXT
- `updated_at` TIMESTAMP
Ejemplos: `groq_api_key` (secreto), `groq_model`, `palabras_peligro` (JSON), `mensaje_guardrail`.

### `prompts_proyecto`
Contexto pedagógico específico por proyecto.
- `id` INT PK
- `proyecto_id` INT FK → `proyectos(id)` CASCADE
- `prompt_contexto` TEXT
- `conocimientos_previos` TEXT
- `enfoque_pedagogico` TEXT
- `preguntas_frecuentes` TEXT
- `activo` BOOL

### `ia_sesiones`
Sesiones de chat anónimas.
- `id` INT PK
- `proyecto_id` INT FK → `proyectos(id)` CASCADE
- `estado` ENUM('activa','cerrada','timeout')
- `fecha_inicio`, `fecha_ultima_interaccion` DATETIME
- Acumulados: `total_mensajes`, `tokens_usados`

### `ia_mensajes`
Histórico de turnos.
- `id` INT PK
- `sesion_id` INT FK → `ia_sesiones(id)` CASCADE
- `rol` ENUM('user','assistant','system')
- `contenido` LONGTEXT
- `tokens` INT
- `metadata` JSON

### `ia_logs`
Trazabilidad de eventos IA.
- `id` INT PK
- `sesion_id` INT FK → `ia_sesiones(id)`
- `proyecto_id` INT FK → `proyectos(id)`
- `tipo_evento` ENUM('respuesta','error','guardrail_activado','cache_hit')
- `descripcion` TEXT
- `tokens_usados` INT, `tiempo_respuesta_ms` INT
- `modelo_usado` VARCHAR(100)
- `costo_estimado` DECIMAL(10,6)
- `fecha_hora` TIMESTAMP

### `ia_respuestas_cache`
Cache por `proyecto_id` + `pregunta_normalizada`.
- `id` INT PK
- `proyecto_id` INT FK → `proyectos(id)` CASCADE
- `pregunta_normalizada` VARCHAR(500)
- `respuesta` LONGTEXT
- `activa` BOOL
- `veces_usada` INT, `ultima_vez_usada` DATETIME

### `ia_stats_proyecto`
Contadores por proyecto (UPDATES en `sp_registrar_interaccion_ia`).
- `proyecto_id` INT PK FK → `proyectos(id)` CASCADE
- `total_consultas` INT
- `total_sesiones` INT
- `tokens_totales` INT
- `ultima_consulta` DATETIME

---

## Administrativo CTeI

### `contratos`
Gestión contractual departamental.
- `id` INT PK
- `numero_contrato` VARCHAR(100)
- `entidad_contratante` VARCHAR(255)
- `departamento` VARCHAR(100)
- Fechas: `fecha_inicio`, `fecha_fin` DATE
- `valor_contrato` DECIMAL(15,2)
- `objeto_contrato` TEXT
- Alcance: `ie_beneficiarias` INT, `estudiantes_estimados` INT, `docentes_estimados` INT
- Cobertura: `ciclos_incluidos` JSON, `grados_incluidos` JSON
- `estado` ENUM('borrador','activo','ejecucion','finalizado')
- `created_at`, `updated_at` TIMESTAMP

### `contrato_proyectos`
Alcance por proyecto.
- `contrato_id` INT FK → `contratos(id)` CASCADE
- `proyecto_id` INT FK → `proyectos(id)`
- `cantidad_kits` INT
PK compuesta `(contrato_id, proyecto_id)`

### `justificacion_ctei`
Documento técnico del contrato.
- `contrato_id` INT PK FK → `contratos(id)` CASCADE
- `justificacion_ctei` TEXT
- `actividades_decreto_591` JSON
- `alineacion_ley_1286` TEXT
- `competencias_men_globales` JSON
- `metodologia_pedagogica` TEXT
- `componente_innovacion` TEXT
- `indicadores_propuestos` JSON
- `metas_propuestas` JSON

### `lotes_kits`
Producción y asignación de kits.
- `id` INT PK
- `codigo_lote` VARCHAR(100) UNIQUE
- `proyecto_id` INT FK → `proyectos(id)`
- `contrato_id` INT FK → `contratos(id)`
- `cantidad` INT
- `fecha_produccion` DATE
- `estado` ENUM('producido','bodega','despachado','entregado')
- `created_at` TIMESTAMP

### `entregas`
Actas de entrega por IE.
- `id` INT PK
- `contrato_id` INT FK → `contratos(id)`
- IE: `institucion_educativa` VARCHAR(255), `codigo_dane` VARCHAR(50), `municipio` VARCHAR(100), `direccion` TEXT
- Entrega: `fecha_entrega` DATETIME, `responsable_entrega`, `responsable_recepcion`, `cargo_recepcion` VARCHAR(255)
- Evidencia: `observaciones` TEXT, `evidencia_fotografica` JSON, `firma_digital` VARCHAR(255), `acta_generada` VARCHAR(255)
- `created_at` TIMESTAMP

### `entrega_lotes`
Detalle por lote en cada entrega.
- `entrega_id` INT FK → `entregas(id)` CASCADE
- `lote_id` INT FK → `lotes_kits(id)`
- `cantidad_entregada` INT
PK compuesta `(entrega_id, lote_id)`

---

## Analítica (anónima)

### `analytics_visitas`
- `id` BIGINT PK
- `proyecto_id` INT NULL
- `tipo_pagina` ENUM('home','catalogo','proyecto','material','busqueda')
- `url_visitada` VARCHAR(500)
- `fecha_hora` TIMESTAMP
- Geo: `pais`, `departamento`, `ciudad` VARCHAR(100)
- `dispositivo` ENUM('mobile','tablet','desktop'), `navegador` VARCHAR(100)
- `sesion_hash` VARCHAR(64)

### `analytics_interacciones`
- `id` BIGINT PK
- `proyecto_id` INT NULL
- `tipo_interaccion` ENUM('descarga_pdf','consulta_ia','click_material','compartir')
- `detalles` JSON
- `fecha_hora` TIMESTAMP
- `sesion_hash` VARCHAR(64)

---

## Vistas

### `v_proyecto_contexto_ia`
Compone en una sola fila el contexto IA del proyecto: core del proyecto (`proyectos`) + taxonomías (`areas`, `competencias`) + guía activa (`guias`) + prompt pedagógico (`prompts_proyecto`).
- Uso: backend `api/ia-consulta.php` y `sp_obtener_contexto_proyecto`.

### `v_proyecto_materiales_detalle`
Lista materiales del proyecto con detalles de seguridad y categoría.
- Uso: renderizado de sección de materiales y validaciones de seguridad.

---

## Procedimientos y Función

### `sp_obtener_contexto_proyecto(p_proyecto_id INT)`
Devuelve:
1) `v_proyecto_contexto_ia` filtrada
2) `v_proyecto_materiales_detalle` filtrada
3) `recursos_multimedia` ordenados

### `sp_registrar_interaccion_ia(p_sesion_id, p_proyecto_id, p_pregunta, p_respuesta, p_tokens, p_tiempo_ms, p_modelo, p_costo, p_guardrail_activado)`
- Inserta mensajes user/assistant
- Actualiza acumulados de sesión
- Loggea evento IA y, si aplica, guardrail
- Actualiza `ia_stats_proyecto` (UPSERT)

### `sp_limpiar_sesiones_antiguas()`
- Marca `timeout` sesiones inactivas > 1 hora
- Limpieza opcional de logs > 90 días (comentado)

### `sp_buscar_respuesta_cache(p_proyecto_id, p_pregunta VARCHAR(500))`
- Normaliza pregunta y busca coincidencia exacta en cache
- Si encuentra, incrementa `veces_usada` y `ultima_vez_usada`

### `fn_es_pregunta_peligrosa(pregunta TEXT)`
- Itera palabras en `configuracion_ia.palabras_peligro` (JSON)
- Retorna `TRUE` si hay match (`LIKE`), `FALSE` en caso contrario

---

## Relaciones (Resumen)
- Proyecto 1—N Guía(s): `guias(proyecto_id)` (solo una activa)
- Proyecto N—N Áreas: `proyecto_areas`
- Proyecto N—N Competencias: `proyecto_competencias`
- Proyecto N—N Materiales: `proyecto_materiales`
- Proyecto 1—N Recursos: `recursos_multimedia`
- Contrato N—N Proyecto: `contrato_proyectos`
- Contrato 1—1 Justificación: `justificacion_ctei`
- Contrato 1—N Lotes: `lotes_kits`
- Entrega N—N Lotes: `entrega_lotes`
- IA: Proyecto 1—N Sesiones, Sesión 1—N Mensajes; Logs referencian sesión y proyecto

---

## Índices y Performance (resumen)
- Claves compuestas: `proyecto_areas`, `proyecto_competencias`, `proyecto_materiales`, `contrato_proyectos`, `entrega_lotes`, `ia_stats_proyecto`
- Índices por FK frecuentes: `proyecto_id` en tablas satélite, `sesion_id` en `ia_mensajes`, `contrato_id` en CTeI
- Uso de vistas para reducir JOINs costosos en IA
- JSON en campos de configuración y listas controladas para flexibilidad sin proliferar tablas

---

## Mapeo de Adaptación — thegreenalmanac → clasedeciencia

- `articles` → `proyectos`
- `sections` → `ciclo` y `grados` (campos en `proyectos`)
- `tags` → `areas` y `competencias` (dos taxonomías separadas)
- `article_materials` → `proyecto_materiales`
- `materials` / `material_categories` → `materiales` / `categorias_materiales`
- `images` / `media` → `recursos_multimedia`
- Admin stats/clicks → `analytics_visitas` / `analytics_interacciones`
- No existente en TGA: Módulos IA y CTeI (`configuracion_ia`, `ia_*`, `contratos`, etc.)

Código a adaptar típicamente:
- Listado/catalogo: JOINs y filtros pasan de `articles/tags/sections` a `proyectos` + `proyecto_areas` + filtros por campos nativos (`ciclo`, `dificultad`, `duracion_minutos`)
- Página detalle: `article.php` → `proyecto.php?slug=` integrando guía (`guias`) y multimedia (`recursos_multimedia`)
- Material detail: `material.php` mantiene patrón, pero fuente es `materiales`
- Búsqueda: preferir búsqueda por `nombre`/`resumen` y relaciones (`areas`) con prepared statements

---

## Buenas Prácticas de Uso (para desarrollo)
- Siempre `PDO` con `prepare()` y placeholders `?`
- Escapar salida HTML con `htmlspecialchars(…, ENT_QUOTES, 'UTF-8')`
- Validar JSON antes de decodificar (campos JSON)
- Controlar sesiones IA (timeouts, límites de tokens)
- Registrar analytics sin datos personales; usar `sesion_hash`

---

## Glosario Rápido
- Ciclo: Nivel educativo (1: 6°-7°, 2: 8°-9°, 3: 10°-11°)
- Competencias MEN: Indagación, Explicación, Uso del Conocimiento
- Guardrails IA: Reglas de seguridad para respuestas (palabras peligro, mensajes preventivos)

---

## Referencias
- Vistas: `v_proyecto_contexto_ia`, `v_proyecto_materiales_detalle`
- Procedimientos: `sp_obtener_contexto_proyecto`, `sp_registrar_interaccion_ia`, `sp_limpiar_sesiones_antiguas`, `sp_buscar_respuesta_cache`
- Función: `fn_es_pregunta_peligrosa`

Este documento se mantiene junto al repositorio para servir de guía durante la adaptación del código y futuras ampliaciones del esquema.