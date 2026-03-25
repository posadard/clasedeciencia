# Plan Maestro: Módulos Administrativos + Asistente IA Backend

## 1. Objetivo
Construir los módulos administrativos clave de la plataforma para que el asistente IA backend pueda operar con datos completos, trazables y confiables en la gestión de contratos, órdenes, entregas y lotes.

Principio rector:
Primero sistema operativo administrativo sólido. Después IA sobre evidencia real.

## 2. Alcance
Este plan cubre:
- Módulo de Contratos CTeI
- Módulo de Entregas
- Módulo de Lotes e Inventario
- Integración de datos para consultas administrativas con IA
- Gobierno de datos, seguridad operativa y trazabilidad

Este plan no cubre:
- Rediseño visual completo del sitio público
- Integraciones externas complejas (ERP/LMS) en primera etapa
- Automatizaciones avanzadas con acciones autónomas de IA

## 3. Estado actual resumido
- Existe infraestructura de IA backend (endpoint, configuración y logs).
- Existen pantallas base para contratos, entregas y lotes, pero están en construcción.
- Existen tablas base para contratos y entregas; lotes requiere verificación/fortalecimiento del modelo en dump principal.
- La IA ya puede responder en backend, pero su valor está limitado por falta de procesos administrativos completos.

## 4. Resultado esperado
Al finalizar el plan:
- El equipo administrativo podrá operar contratos, entregas y lotes de punta a punta.
- Habrá trazabilidad por entidad, estado, responsable, fecha y evidencia.
- El asistente IA administrativo responderá preguntas operativas con contexto real y verificable.
- Se reducirá tiempo de soporte y consulta manual para gestión interna.

## 5. Requisitos IA-Ready (obligatorios)
Para habilitar IA útil y confiable, cada módulo debe cumplir:

1. Datos estructurados y completos
- IDs, estados, fechas, responsables, valores y relaciones obligatorias.

2. Flujo de estados explícito
- Estados válidos, transiciones válidas y reglas de negocio por transición.

3. Evidencias y soporte documental
- Actas, anexos, observaciones y metadatos mínimos.

4. Auditabilidad
- Bitácora de cambios por usuario, acción, fecha y campos afectados.

5. Consultabilidad
- Consultas/resúmenes orientados a preguntas frecuentes administrativas.

6. Seguridad operativa
- Validación de permisos, controles CSRF, sanitización y manejo de errores seguro.

## 6. Arquitectura funcional del asistente administrativo IA
La IA backend operará en modo asistente consultivo con estas capacidades:
- Consultar estado de contratos, entregas y lotes.
- Detectar inconsistencias básicas (faltantes, desbalances, pendientes críticos).
- Resumir situación operativa por periodo, contrato, entidad o departamento.
- Ayudar a redactar comunicaciones administrativas internas.

La IA backend no debe:
- Ejecutar cambios críticos automáticamente en primera versión.
- Confirmar datos no existentes en BD.
- Emitir decisiones contractuales sin validación humana.

## 7. Diseño funcional por módulo

### 7.1 Módulo Contratos CTeI
Objetivo:
Gestionar ciclo de vida contractual y su ejecución administrativa.

Campos mínimos sugeridos:
- numero_contrato
- entidad_contratante
- departamento
- municipio
- fecha_inicio
- fecha_fin
- valor_total
- valor_ejecutado
- estado_contrato (borrador, vigente, suspendido, finalizado, cerrado)
- supervisor
- objeto_contrato
- observaciones

Funciones mínimas:
- Crear, editar, listar, filtrar, ver detalle
- Cambios de estado con validaciones
- Asociación con entregas y lotes
- Alertas por vencimiento y avance financiero

Preguntas IA que debe soportar:
- Cuáles contratos están próximos a vencer
- Cuál es el avance por contrato
- Qué contratos tienen entregas atrasadas
- Qué contratos no tienen soporte documental suficiente

### 7.2 Módulo Entregas
Objetivo:
Registrar y controlar entregas de kits con trazabilidad institucional.

Campos mínimos sugeridos:
- contrato_id
- institucion_educativa
- departamento
- municipio
- fecha_programada
- fecha_entrega
- estado_entrega (programada, en_transito, entregada, rechazada, reprogramada)
- responsable_entrega
- responsable_recepcion
- acta_pdf
- observaciones

Funciones mínimas:
- CRUD y filtros por estado/fecha/territorio
- Registro de novedades
- Asociación a lotes entregados
- Validación de consistencia con contrato

Preguntas IA que debe soportar:
- Entregas pendientes esta semana
- Entregas reprogramadas y causa
- Instituciones con mayor retraso
- Entregas sin acta o con evidencia incompleta

### 7.3 Módulo Lotes e Inventario
Objetivo:
Controlar unidades por lote desde disponibilidad hasta entrega.

Campos mínimos sugeridos:
- codigo_lote
- kit_id
- contrato_id (opcional según flujo)
- cantidad_total
- cantidad_disponible
- cantidad_asignada
- cantidad_entregada
- fecha_fabricacion
- fecha_caducidad (si aplica)
- estado_lote (activo, bloqueado, agotado, cerrado)
- ubicacion
- observaciones

Funciones mínimas:
- Alta y edición de lotes
- Asignación de lotes a entregas
- Conciliación de stock
- Historial de movimientos

Preguntas IA que debe soportar:
- Qué lotes están por agotarse
- Qué entregas no tienen lote asignado
- Diferencias entre stock teórico y asignado
- Riesgo de quiebre por contrato/departamento

## 8. Modelo de datos y relaciones (meta)
Relaciones clave a consolidar:
- contrato 1:N entregas
- contrato 1:N lotes (directa o indirecta según negocio)
- lote N:1 kit
- entrega N:M lotes (si una entrega puede llevar múltiples lotes)

Regla de oro:
La IA debe consultar vistas y relaciones consistentes, no tablas aisladas sin contexto.

## 9. Vistas y consultas orientadas a IA
Crear una capa de consulta para IA con:
- Resumen de contratos
- Resumen de entregas con estado y atraso
- Resumen de lotes con disponibilidad
- Vista de riesgo operativo (vencimientos, pendientes, faltantes)
- Vista de trazabilidad por contrato

Beneficio:
Menor latencia, mayor precisión y respuestas administrativas más útiles.

## 10. Seguridad y gobernanza
Controles obligatorios:
- Autenticación admin estricta
- Eliminación de bypass de autenticación en producción
- CSRF en formularios administrativos
- Sanitización y escape de salida
- Logs de actividad de usuario y logs IA
- Política de retención de logs

## 11. Plan por fases

### Fase 0. Definición funcional y datos (1 semana)
Entregables:
- Diccionario de datos final de contratos, entregas y lotes
- Flujos de estado con reglas y validaciones
- Catálogo de preguntas IA administrativas prioritarias

Criterio de salida:
- Modelo funcional y de datos aprobado por negocio y operación.

### Fase 1. Contratos operativos (1-2 semanas)
Entregables:
- CRUD completo de contratos
- Filtros, estados, detalle y validaciones
- Auditoría básica de cambios

Criterio de salida:
- El equipo puede administrar contratos sin procesos manuales paralelos.

### Fase 2. Entregas operativas (1-2 semanas)
Entregables:
- CRUD completo de entregas
- Flujo de estados de entrega
- Asociación con contratos y evidencias

Criterio de salida:
- Trazabilidad de entrega por institución y contrato.

### Fase 3. Lotes e inventario (2 semanas)
Entregables:
- CRUD de lotes
- Asignación a entregas
- Conciliación de cantidades

Criterio de salida:
- Visibilidad real de stock, asignación y disponibilidad.

### Fase 4. Capa IA-ready (1 semana)
Entregables:
- Vistas/consultas consolidadas para IA
- Mapeo contexto_pagina -> consultas y métricas
- Prompts administrativos ajustados a datos reales

Criterio de salida:
- Respuestas IA con evidencia operacional y precisión consistente.

### Fase 5. Estabilización y operación (1 semana)
Entregables:
- Checklist de QA administrativo
- Pruebas de casos críticos
- Métricas de calidad de respuesta IA
- Guía de uso para equipo administrativo

Criterio de salida:
- Operación estable, trazable y con IA útil para soporte diario.

## 12. KPIs de éxito
KPIs operativos:
- Porcentaje de contratos con trazabilidad completa
- Porcentaje de entregas con soporte documental completo
- Precisión de stock por lote
- Tiempo medio de respuesta a consultas internas

KPIs IA:
- Tasa de respuesta útil (según validación interna)
- Tasa de alucinación detectada
- Tiempo promedio de respuesta
- Cobertura de preguntas frecuentes administrativas

## 13. Riesgos y mitigaciones
Riesgo: datos incompletos en captura inicial.
Mitigación: validaciones obligatorias y reglas de estado.

Riesgo: respuestas IA sin base real.
Mitigación: contexto estricto basado en vistas consolidadas.

Riesgo: inconsistencias entre módulos.
Mitigación: relaciones y reglas de negocio unificadas.

Riesgo: sobrecarga operativa al equipo.
Mitigación: implementación incremental por fases y capacitación.

## 14. Decisión recomendada
Avanzar de inmediato con Fase 0 y Fase 1.
No escalar el asistente administrativo IA a uso amplio hasta completar al menos:
- Contratos + Entregas operativos
- Capa mínima de consultas IA-ready
- Controles de seguridad y auditoría activos

---

Documento: Plan de implementación administrativa IA
Ubicación: marco
Fecha: 2026-03-24
Estado: Propuesto para ejecución por fases
