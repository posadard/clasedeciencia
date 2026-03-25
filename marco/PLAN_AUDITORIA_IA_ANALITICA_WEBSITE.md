# Plan Maestro de Auditoría IA + Analítica Web

## 1. Objetivo
Construir una base de auditoría real del website para que el asistente IA (frontend y admin) responda con datos verificables, trazables y actualizados, evitando respuestas genéricas por falta de contexto de métricas.

Principio rector:
Primero telemetría y modelo de sesiones confiables. Después decisiones y recomendaciones con IA.

## 2. Problema a resolver
Estado actual observado:
- El panel IA conserva continuidad principalmente en cliente (sessionStorage), no como sesión conversacional persistente integral de negocio.
- Existen tablas de IA y analítica, pero la cobertura de eventos de navegación y conversión del website no es homogénea.
- Faltan vistas analíticas orientadas a preguntas de auditoría del negocio (ejemplo: clases más vistas, embudo de navegación, caída de interacción por ciclo/grado).
- El asistente backend puede consultar operaciones, pero no siempre tiene un bloque analítico de tráfico/uso para responder preguntas de popularidad y desempeño de contenido.

## 3. Alcance
Este plan cubre:
- Modelo unificado de sesiones IA (frontend + backend)
- Instrumentación de eventos de analítica web y operativa
- Capa de vistas para auditoría y consumo de IA
- Gobierno de datos, calidad y trazabilidad
- KPIs de auditoría accionables en Admin

Este plan no cubre:
- Rediseño completo UI pública
- Automatización autónoma de decisiones sin revisión humana
- Integración de herramientas externas avanzadas (fase posterior)

## 4. Resultado esperado
Al finalizar:
- La IA conserva continuidad real entre páginas y contexto de conversación por sesión.
- El admin puede preguntar: "qué clases son las más vistas" y recibir ranking verificable por periodo.
- Existe auditoría web operativa: tráfico, búsqueda, clics, IA, conversión de contenido y alertas de anomalía.
- Las respuestas IA incluyen base de evidencia (fuente de vista y fecha de corte).

## 5. Diseño objetivo de sesiones IA

### 5.1 Sesión unificada por instancia
- Frontend: sesión anónima con hash/cookie persistente.
- Backend: sesión asociada a usuario admin autenticado.
- Ambas instancias registran mensajes y eventos en tablas de sesión/mensajes/logs.

### 5.2 Continuidad de conversación
- Persistencia dual:
  1) Cliente (rápida UX)
  2) Servidor (histórico auditable)
- Recuperación al cambiar de página dentro del mismo dominio/contexto.
- Resumen de contexto por sesión para mantener costos de tokens controlados.

### 5.3 Segmentación de memoria
- Historial separado por instancia y contexto:
  - frontend_general
  - frontend_por_clase
  - admin_global
  - admin_por_modulo (contratos, entregas, lotes, etc.)

## 6. Taxonomía de eventos para auditoría real
Definir y estandarizar un catálogo de eventos con schema estable.

### 6.1 Eventos mínimos del website
- page_view
- search_query
- search_result_click
- ia_question
- ia_answer
- ia_error
- ia_guardrail
- cta_click (botones clave)
- content_engagement (scroll/tiempo lectura opcional)

### 6.2 Eventos operativos admin
- admin_entity_create
- admin_entity_update
- admin_entity_delete
- admin_report_export
- admin_ia_question
- admin_ia_answer

### 6.3 Dimensiones obligatorias por evento
- timestamp
- session_id
- instancia (frontend/backend)
- tipo_pagina/modulo
- entidad_tipo
- entidad_id
- user_agent resumido
- referrer
- departamento (si aplica)
- dispositivo
- metadata JSON validado

## 7. Modelo analítico para preguntas de negocio
Crear vistas orientadas a decisiones y prompts IA.

### 7.1 Vistas de popularidad de contenido
- v_auditoria_top_clases_7d
- v_auditoria_top_clases_30d
- v_auditoria_top_kits_30d
- v_auditoria_top_componentes_30d

Campos sugeridos:
- entidad_id
- nombre/slug
- visitas_totales
- sesiones_unicas
- clics_relacionados
- tendencia_vs_periodo_previo

### 7.2 Vistas de embudo
- v_auditoria_funnel_home_clase
- v_auditoria_funnel_clase_kit
- v_auditoria_funnel_busqueda_click

### 7.3 Vistas de salud IA
- v_auditoria_ia_calidad_respuesta
- v_auditoria_ia_latencia
- v_auditoria_ia_guardrails
- v_auditoria_ia_preguntas_sin_resolver

### 7.4 Vista de riesgos
- v_auditoria_riesgo_operativo_consolidado

## 8. Integración del contexto analítico en IA

### 8.1 Regla de contexto obligatorio para consultas de métricas
Cuando la pregunta del usuario sea de analítica/auditoría (más vistas, tendencia, conversión, caída), la IA debe:
1. Consultar vistas de auditoría correspondientes
2. Responder con datos numéricos y periodo
3. Citar la fuente (vista) y fecha de corte
4. Si no hay datos, reportar brecha explícita de cobertura

### 8.2 Plantilla de respuesta IA para auditoría
Formato recomendado:
- Resumen ejecutivo (1-2 líneas)
- Tabla/ranking breve
- Hallazgos principales
- Riesgos detectados
- Recomendaciones accionables
- Fuente de datos y corte

## 9. Calidad de datos y gobernanza

### 9.1 Reglas de calidad
- Completitud: eventos con campos obligatorios >= 98%
- Consistencia: catálogo de eventos sin variantes semánticas duplicadas
- Frescura: retraso máximo de consolidación <= 15 min
- Integridad: claves de sesión y entidad válidas

### 9.2 Controles de privacidad
- IP anonimizada cuando aplique
- Sin PII sensible en metadata libre
- Retención y purga por política definida
- Trazabilidad de acceso a datos analíticos admin

### 9.3 Auditoría de confiabilidad IA
- Tasa de respuestas con evidencia
- Tasa de "no dispongo de datos"
- Tasa de contradicción con dashboard
- Tiempo de respuesta por consulta

## 10. KPIs principales de auditoría

KPI de uso web:
- Sesiones únicas por día/semana
- Top 10 clases por visitas y por sesiones únicas
- CTR de búsqueda (consulta -> clic)
- Tasa de navegación Home -> Clase -> Kit

KPI de IA:
- Consultas IA por instancia
- Respuestas con evidencia de datos
- Guardrails activados
- Latencia promedio y p95
- Preguntas de negocio no resueltas

KPI operativos admin:
- Tiempo medio de cierre de incidencias operativas
- Volumen de acciones por módulo
- Alertas críticas abiertas vs resueltas

## 11. Roadmap por fases

### Fase 0: Definición y baseline (1 semana)
Entregables:
- Diccionario de eventos
- Mapa de entidades y sesión
- Definición de KPIs y periodos
- Tablero baseline inicial

Criterio de salida:
- Métricas y eventos acordados por operación + producto.

### Fase 1: Instrumentación tracking (1-2 semanas)
Entregables:
- Captura homogénea de eventos frontend/admin
- Session_id unificado por instancia
- Persistencia robusta de mensajes IA en servidor

Criterio de salida:
- Cobertura de eventos críticos >= 90%.

### Fase 2: Capa analítica (1-2 semanas)
Entregables:
- Vistas top/funnel/salud IA/riesgo
- Consultas optimizadas con índices
- Validación de consistencia contra datos fuente

Criterio de salida:
- Preguntas de auditoría base respondibles por SQL con evidencia.

### Fase 3: IA con evidencia (1 semana)
Entregables:
- Inyección automática de bloques analíticos a prompts backend
- Reglas de respuesta con fuente y corte
- Fallback explícito de brecha de datos

Criterio de salida:
- La IA responde ranking y tendencias de forma verificable.

### Fase 4: Observabilidad y alertas (1 semana)
Entregables:
- Dashboard de auditoría en admin
- Alertas por anomalías (caídas, picos de error, guardrails)
- Reporte semanal automatizable

Criterio de salida:
- Auditoría operativa continua y accionable.

## 12. Criterios de aceptación global
- Se puede responder con evidencia la pregunta: "qué clases son las más vistas (7d/30d)".
- La conversación IA no se reinicia al cambiar de página dentro de la misma sesión esperada.
- Los reportes admin concilian con vistas SQL oficiales.
- El sistema identifica y comunica brechas de datos en vez de inventar respuestas.

## 13. Riesgos y mitigación
Riesgo: baja cobertura de eventos al inicio.
Mitigación: rollout por prioridad (page_view, search, ia_question, ia_answer).

Riesgo: alto costo tokens por historial largo.
Mitigación: resumen incremental de sesión + ventana deslizante de mensajes.

Riesgo: respuestas IA sin evidencia.
Mitigación: política de prompt con formato obligatorio y bloque de fuente.

Riesgo: desalineación entre esquema y consultas admin.
Mitigación: contrato de nombres de columnas y pruebas de smoke SQL por release.

## 14. Checklist de implementación
- [ ] Diccionario de eventos aprobado
- [ ] Session_id unificado frontend/backend
- [ ] Persistencia servidor de mensajes IA activa
- [ ] Vistas analíticas de popularidad y funnel creadas
- [ ] Bloques de contexto analítico integrados en IA backend
- [ ] Dashboard auditoría en admin operativo
- [ ] Alertas de calidad de datos activas
- [ ] Documentación operativa y de validación publicada

## 15. Decisión recomendada inmediata
Priorizar en el siguiente sprint:
1. Sessiones IA persistentes en backend y separación de memoria por instancia/contexto.
2. Instrumentación de page_view + search + IA (pregunta/respuesta) con dimensiones estándar.
3. Vistas top clases 7d/30d y consumo obligatorio por IA cuando se pregunten métricas.

Con estas 3 acciones, la plataforma pasa de asistente "conversacional" a asistente "auditable" para decisiones reales del website.
