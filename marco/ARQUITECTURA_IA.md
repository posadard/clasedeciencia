# Arquitectura del Sistema IA — Clase de Ciencia

**Versión:** 1.0  
**Fecha:** Marzo 2026  
**Estado:** Definida, pendiente de implementación

---

## 1. Visión general

El sistema IA de clasedeciencia.com se compone de **dos instancias independientes** que comparten la misma infraestructura de código pero con configuración, modelos, prompts y scopes de datos diferenciados:

| Instancia | Usuarios | Contexto | Safety |
|---|---|---|---|
| `frontend` | Estudiantes (6°–11°) | Clase activa + kit + guía | Estricto |
| `backend` | Admin / Docentes | Entidad admin activa | Moderado |

---

## 2. Estrategia de contexto: Context Injection (CI)

### Por qué CI y no RAG ni Function Calling

**RAG descartado** porque:
- Requiere API de embeddings (coste y latencia adicionales)
- Requiere vector DB (Pinecone, Weaviate) — infraestructura no disponible en el hosting MySQL
- El contenido está en SQL estructurado, no en documentos libres; RAG es overkill

**Function Calling descartado** porque:
- Múltiples round-trips a la API (request → tool call → resultado → respuesta final)
- llama-3.x tool calling es menos confiable que GPT-4
- `openai/gpt-oss-20b` en Groq no soporta tools
- Innecesariamente complejo para el volumen de datos actual

**Context Injection elegida** porque:
- PHP construye un bloque de texto estructurado con datos de MySQL **antes** de llamar a Groq
- El contexto se inyecta en el `system prompt`; la IA recibe todo lo relevante de una vez
- Sin round-trips extra, sin dependencias adicionales
- Los modelos llama-3.x y openai/gpt-oss responden muy bien a contexto textual estructurado

---

## 3. Instancia Frontend (Estudiantes)

### Activación
El widget `asistente-ia.js` envía `clase_id` + `pregunta` + `instancia: 'frontend'` al endpoint `api/ia-consulta.php`.

### Modelos Groq
| Prioridad | Modelo | Velocidad | Rol |
|---|---|---|---|
| Principal | `llama-3.3-70b-versatile` | 280t/s | Respuestas pedagógicas completas |
| Fallback 1 | `llama-3.1-8b-instant` | 560t/s | Si el principal da 429/503 |
| Fallback 2 | `openai/gpt-oss-20b` | 1000t/s | Último recurso |

### Parámetros recomendados
```
temperature: 0.5        # Respuestas consistentes y seguras
max_completion_tokens: 800
top_p: 0.9
```

### Bloque de contexto a inyectar (PHP construye esto)

| Fuente SQL | Campos | Tokens ~est. |
|---|---|---|
| `clases` | nombre, ciclo, grados, dificultad, duracion_minutos, resumen, objetivo_aprendizaje, seguridad | 300 |
| `clase_areas` + `areas` | nombre de cada área del conocimiento | 50 |
| `clase_competencias` + `competencias` | nombre + explicación de competencias MEN | 150 |
| `prompts_clase` | prompt_contexto, enfoque_pedagogico, conocimientos_previos, preguntas_frecuentes | 400 |
| `v_clase_kits_detalle` | kit_nombre, item_nombre, cantidad, notas por componente | 400 |
| `kit_items.advertencias_seguridad` | advertencias de seguridad por componente | 200 |
| `kit_manuals.pasos_json` | pasos del manual del kit principal | 600 |
| `guias.pasos` | pasos de la guía de la clase | 300 |
| **Total estimado** | | **~2.400 tokens** |

### Queries PHP necesarias para construir el contexto
```php
// 1. Contexto base (ya existe)
SELECT * FROM v_clase_contexto_ia WHERE clase_id = ?

// 2. Materiales del kit (ya existe)
SELECT * FROM v_clase_kits_detalle WHERE clase_id = ?

// 3. Prompt pedagógico (query directa)
SELECT prompt_contexto, enfoque_pedagogico, conocimientos_previos, preguntas_frecuentes
FROM prompts_clase WHERE clase_id = ? AND activo = 1 LIMIT 1

// 4. Pasos del manual del kit principal
SELECT km.pasos_json, km.seguridad_json
FROM kit_manuals km
JOIN clase_kits ck ON ck.kit_id = km.kit_id
WHERE ck.clase_id = ? AND ck.es_principal = 1 AND km.status = 'published'
LIMIT 1

// 5. Pasos de la guía
SELECT pasos, explicacion_cientifica FROM guias WHERE clase_id = ? LIMIT 1
```

### Guardrails (estrictos)
- `palabras_peligro`: lista configurable desde el admin (JSON)
- `palabras_tematicas`: palabras fuera del ámbito científico escolar (ej: política, violencia)
- `nivel_safety`: `estricto` — se activa guardrail con cualquier coincidencia
- Respuesta de guardrail sustituye completamente la llamada a Groq
- Todo guardrail se registra en `ia_guardrails_log`

### Caché
- `ia_respuestas_cache` (existe): keyed por `clase_id + pregunta_normalizada`
- Solo aplica para instancia `frontend`
- Trigger existente actualiza `ia_logs` cuando se usa caché

---

## 4. Instancia Backend (Admin)

### Activación
El panel admin encía `contexto_pagina` + `pregunta` + `instancia: 'backend'` + opcional `entidad_tipo` + `entidad_id` al mismo endpoint `api/ia-consulta.php`.

### Modelos Groq
| Prioridad | Modelo | Velocidad | Rol |
|---|---|---|---|
| Principal | `openai/gpt-oss-20b` | 1000t/s | Respuestas técnicas rápidas |
| Fallback 1 | `llama-3.3-70b-versatile` | 280t/s | Si el principal da 429/503 |
| Fallback 2 | `llama-3.1-8b-instant` | 560t/s | Último recurso |

### Parámetros recomendados
```
temperature: 0.3        # Respuestas factuales y precisas
max_completion_tokens: 1200
top_p: 0.95
```

### Page-aware scoping

El parámetro `contexto_pagina` determina qué datos PHP inyecta:

| `contexto_pagina` | Tablas consultadas | Datos inyectados |
|---|---|---|
| `dashboard` | `clases`, `kits`, `contratos`, `entregas` | Conteos generales + últimas actividades |
| `clases` | `clases`, `ciclos`, `clase_areas`, `clase_kits` | Lista completa con status, ciclo, areas, kit asignado |
| `kits` | `kits`, `clase_kits`, `kit_componentes`, `kit_items` | Lista con componentes resumen |
| `componentes` | `kit_items`, `kit_componentes`, `categorias_items` | Lista de componentes con advertencias |
| `contratos` | `contratos` | Todos los contratos (numero, entidad, dpto, valor, fecha) |
| `entregas` | `entregas`, `contratos` | Entregas con contrato vinculado + institución + fecha |
| `lotes` | `lotes` | Stock de lotes por kit |
| `manuales` | `kit_manuals`, `kits` | Lista de manuales con status + tipo |
| `ia` | `v_ia_dashboard`, `ia_guardrails_log`, `ia_stats_clase` | Stats de uso + errores recientes + alertas |

### Contexto profundo (entidad específica)
Si se envía `entidad_tipo` + `entidad_id`, PHP agrega el registro completo de esa entidad:

```
entidad_tipo: 'contrato'  → SELECT * + sus entregas
entidad_tipo: 'kit'       → SELECT * + sus componentes + clases asociadas
entidad_tipo: 'clase'     → SELECT * + v_clase_contexto_ia completo
entidad_tipo: 'entrega'   → SELECT * + su contrato
```

**Total estimado por consulta backend: 1.500–4.000 tokens** según página.

### Guardrails (moderados)
- Sin guardrail de palabras peligro (el admin es adulto)
- Solo guardrail de jailbreak / instrucciones maliciosas (configurable)
- `nivel_safety`: `moderado`
- La IA puede responder preguntas técnicas que el frontend rechazaría

---

## 5. Flujo de llamada unificado (api/ia-consulta.php)

```
POST /api/ia-consulta.php
{
  "instancia": "frontend" | "backend",
  "clase_id": 3,              // frontend: requerido
  "contexto_pagina": "contratos", // backend: requerido
  "entidad_tipo": "contrato", // backend: opcional
  "entidad_id": 5,            // backend: opcional
  "pregunta": "¿Qué pasa si...?"
}
```

```
Flujo PHP:
1. Leer instancia del request
2. Cargar config de configuracion_ia WHERE instancia = ?
3. Verificar ia_activa
4. Construir bloque de contexto según instancia:
   - frontend → queries por clase_id
   - backend  → queries por contexto_pagina (+ entidad si aplica)
5. Evaluar guardrails (según nivel_safety de la instancia)
6. Si frontend: verificar caché
7. Llamar a Groq: intentar modelo_1 → 429/503 → modelo_2 → modelo_3
8. Registrar en ia_logs (con campo instancia)
9. Si frontend + no guardrail: guardar en caché
10. Retornar respuesta
```

---

## 6. Migraciones SQL requeridas

### ALTER TABLE (añadir instancia a 5 tablas)
```sql
ALTER TABLE configuracion_ia
  ADD COLUMN instancia ENUM('frontend','backend') NOT NULL DEFAULT 'frontend' AFTER id,
  ADD UNIQUE KEY uk_instancia_clave (instancia, clave);

ALTER TABLE ia_sesiones
  ADD COLUMN instancia ENUM('frontend','backend') NOT NULL DEFAULT 'frontend';

ALTER TABLE ia_logs
  ADD COLUMN instancia ENUM('frontend','backend') NOT NULL DEFAULT 'frontend';

ALTER TABLE ia_guardrails_log
  ADD COLUMN instancia ENUM('frontend','backend') NOT NULL DEFAULT 'frontend';

ALTER TABLE ia_mensajes
  ADD COLUMN instancia ENUM('frontend','backend') NOT NULL DEFAULT 'frontend';
```

### Seed de configuracion_ia (~28 filas, 14 por instancia)

Claves por instancia:

| Clave | Tipo | Frontend | Backend |
|---|---|---|---|
| `ia_activa` | booleano | `0` | `0` |
| `groq_api_key` | secreto | (vacío) | (vacío) |
| `groq_model_1` | texto | `llama-3.3-70b-versatile` | `openai/gpt-oss-20b` |
| `groq_model_2` | texto | `llama-3.1-8b-instant` | `llama-3.3-70b-versatile` |
| `groq_model_3` | texto | `openai/gpt-oss-20b` | `llama-3.1-8b-instant` |
| `groq_temperature` | numero | `0.5` | `0.3` |
| `groq_max_tokens` | numero | `800` | `1200` |
| `groq_top_p` | numero | `0.9` | `0.95` |
| `prompt_sistema` | texto | (prompt pedagógico) | (prompt técnico) |
| `guardrails_activos` | booleano | `1` | `0` |
| `palabras_peligro` | json | `["fuego","explosión",...]` | `[]` |
| `palabras_tematicas` | json | `["política","violencia",...]` | `[]` |
| `nivel_safety` | texto | `estricto` | `moderado` |
| `mensaje_guardrail` | texto | (mensaje educativo) | (mensaje técnico) |

---

## 7. Tablas y vistas que NO requieren cambios

Las siguientes ya sirven bien a la arquitectura:

- `v_clase_contexto_ia` — contexto base de clase para frontend
- `v_clase_kits_detalle` — componentes de kit para frontend  
- `v_ia_dashboard` — stats para el panel admin
- `v_ia_preguntas_frecuentes_clase` — preguntas frecuentes por clase
- `ia_respuestas_cache` — solo aplica a frontend (ya diseñado así)
- `ia_stats_clase` — stats por clase (solo frontend)
- `prompts_clase` — 15 filas, una por clase, ya existe

---

## 8. Archivos a crear/modificar

| Archivo | Acción | Prioridad |
|---|---|---|
| `u626603208_clasedeciencia.sql` (o script separado) | SQL: ALTER 5 tablas + INSERT 28 filas seed | 1 — Base |
| `api/ia-consulta.php` | Reescritura: instancia + context builder diferenciado + fallback 3 modelos | 2 — Core |
| `admin/ia/index.php` | Nuevo: panel con 4 tabs (Frontend config, Backend config, Test/Status, Logs) | 3 — Admin |
| `assets/js/asistente-ia.js` | Fix: proyecto_id→clase_id + añadir instancia:'frontend' + mejoras UI | 4 — Widget |

---

## 9. Prompts del sistema (borradores)

### Frontend (pedagógico / estricto)
```
Eres un asistente científico educativo para estudiantes colombianos de secundaria.
Tu misión es GUIAR, no resolver: usa preguntas socráticas para que el estudiante llegue a sus propias conclusiones.
NUNCA des respuestas directas a preguntas de examen o evaluaciones.
SIEMPRE refuerza normas de seguridad antes de cualquier instrucción experimental.
Habla con lenguaje claro, amigable y motivador, apropiado para el ciclo educativo del estudiante.
Si detectas una pregunta fuera del ámbito científico educativo, redirige amablemente al tema.
Contexto de la clase actual:
{CONTEXTO_CLASE}
```

### Backend (técnico / operativo)
```
Eres un asistente técnico y operativo para el equipo administrativo de Clase de Ciencia SAS.
Tienes acceso al estado actual del sistema: contratos CTeI, entregas, lotes, clases y métricas de IA.
Responde con precisión, usa datos concretos cuando estén disponibles en el contexto.
Puedes sugerir acciones administrativas, detectar inconsistencias en datos y ayudar a redactar documentos operativos.
Contexto actual del sistema:
{CONTEXTO_ADMIN}
```

---

## 10. Notas de implementación

- **Fallback de modelos**: detectar HTTP 429 (rate limit) o 503 (model unavailable) en la respuesta curl. Reintentar con `modelo_2`, luego `modelo_3`. Registrar en `ia_logs.modelo_usado` cuál respondió finalmente.
- **Token budget**: el context injection no debe superar el 40% del contexto máximo del modelo para dejar espacio a la respuesta. Con llama-3.3-70b (128K) esto no es un problema práctico con los ~2.400 tokens estimados.
- **Seguridad API key**: `groq_api_key` se almacena con `tipo = 'secreto'` en `configuracion_ia`. En el panel admin se muestra enmascarada (solo últimos 4 caracteres). Nunca se expone en respuestas JSON.
- **La IA del backend NO usa caché** — las respuestas operativas deben ser siempre frescas con los datos actuales.
- **CSRF**: el endpoint `/api/ia-consulta.php` ya es público (sin sesión admin requerida) para el frontend. Para el backend se debe validar que la request viene del admin autenticado (verificar sesión admin PHP).
