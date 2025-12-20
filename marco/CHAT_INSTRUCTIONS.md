# INSTRUCCIONES PARA EL AGENTE DE DESARROLLO - clasedeciencia.com

## CONTEXTO DEL PROYECTO

Estás desarrollando **clasedeciencia.com**, una plataforma educativa de proyectos científicos para estudiantes de grados 6° a 11° en Colombia. La plataforma combina:
- Estructura backend/frontend de `thegreenalmanac.com` 
- Contenido de proyectos científicos similar a `freescienceproject.com`
- Requerimientos definidos en `clasedeciencia_requerimientos_v2.txt`
- Arquitectura completa detallada en `ANALISIS_Y_PLAN_CLASEDECIENCIA.md`

### Filosofía Central
> **El kit físico es la llave**. No hay registro de usuarios, no hay login. La plataforma es pública y abierta.

---

## REGLAS DE TRABAJO CRÍTICAS

### ✅ LO QUE HACES

1. **CODIFICAR ÚNICAMENTE**
   - Crear archivos PHP, HTML, CSS, JavaScript
   - Editar archivos existentes
   - Escribir código limpio, comentado y funcional
   - Seguir la estructura del proyecto existente

2. **USAR console.log() PARA DEBUG**
   - Agregar `console.log()` estratégicos para rastrear flujo
   - Incluir mensajes descriptivos: `console.log('🔍 Filtrando proyectos por ciclo:', ciclo);`
   - Usar emojis para identificar rápido: 🔍 (debug), ⚠️ (warning), ✅ (success), ❌ (error)

3. **DOCUMENTAR TU CÓDIGO**
   - Comentarios explicativos en español
   - Bloques de comentarios para secciones importantes
   - Explicar lógica compleja

4. **SEGUIR PATRONES EXISTENTES**
   - Revisar cómo están hechas páginas similares en `thegreenalmanac.com`
   - Mantener estructura de carpetas definida
   - Usar mismas convenciones (PDO, prepared statements, funciones helper)

### ❌ LO QUE NO HACES

1. **NO EJECUTAR NI PROBAR CÓDIGO**
   - NO usar navegadores ni servidores
   - NO intentar ver resultados en vivo
   - NO hacer testing funcional
   - El usuario probará en su entorno local y reportará errores

2. **NO TOCAR LA BASE DE DATOS**
   - NO crear tablas SQL (el usuario lo hace manualmente)
   - NO ejecutar queries directamente
   - SÍ puedes escribir el SQL como comentario o documentación
   - SÍ puedes escribir código PHP con queries preparadas

3. **NO INSTALAR DEPENDENCIAS**
   - NO usar npm/composer para instalar paquetes
   - Si necesitas una librería, indícalo en comentarios

---

## FLUJO DE TRABAJO

### Cuando el usuario pide una funcionalidad:

```
1. ANALIZAR → Lee código existente relacionado
2. PLANEAR → Explica brevemente qué archivos crearás/editarás
3. CODIFICAR → Escribe el código completo con console.log() incluidos
4. DOCUMENTAR → Explica cómo probarlo en navegador
```

### Cuando el usuario reporta un error:

```
1. PEDIR DETALLES → "¿Qué mensaje aparece en consola de Chrome?"
2. ANALIZAR → Revisar código con el mensaje de error
3. AGREGAR DEBUG → Más console.log() en puntos críticos
4. CORREGIR → Editar el archivo problemático
5. EXPLICAR → "Agrega console.log aquí para ver si llega X valor"
```

---

## ESTRUCTURA DEL PROYECTO

### Carpetas Principales
```
clasedeciencia.com/
├── config.php                 # Configuración DB (NO edites credenciales)
├── index.php                  # Homepage
├── includes/                  # Componentes compartidos
│   ├── header.php
│   ├── footer.php
│   ├── db-functions.php       # Funciones de consulta
│   └── functions.php          # Utilidades
├── admin/                     # Backend administrativo
│   ├── auth.php               # Autenticación
│   ├── dashboard.php
│   └── [módulos]/
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
└── api/                       # Endpoints AJAX
```

### Archivos Base de Referencia
- **thegreenalmanac.com/** → Arquitectura a seguir
- **freescienceproject.com/** → Inspiración de contenido
- **ANALISIS_Y_PLAN_CLASEDECIENCIA.md** → Especificaciones completas

---

## STACK TECNOLÓGICO

### Backend
- **PHP 8.1+** (sin frameworks)
- **PDO** para base de datos
- **Session-based auth** para admin

### Frontend
- **HTML5 + CSS3** (responsive)
- **JavaScript Vanilla** (sin frameworks pesados)
- **Fetch API** para AJAX

### Base de Datos
- **MySQL 8.0+**
- Usuario maneja estructura, tú escribes queries en código

---

## PATRONES DE CÓDIGO

### 1. Conexión a Base de Datos
```php
// Ya existe en config.php - NO recrear
require_once 'config.php';
// $pdo ya está disponible globalmente
```

### 2. Queries con PDO (SIEMPRE Prepared Statements)
```php
// ✅ CORRECTO
$stmt = $pdo->prepare("SELECT * FROM proyectos WHERE ciclo = ? AND activo = ?");
$stmt->execute([$ciclo, 1]);
$proyectos = $stmt->fetchAll();

// ❌ INCORRECTO - SQL Injection vulnerable
$result = $pdo->query("SELECT * FROM proyectos WHERE ciclo = $ciclo");
```

### 3. Estructura de Página Pública
```php
<?php
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

// Variables para header
$page_title = 'Título de la Página';
$page_description = 'Descripción SEO';
$canonical_url = SITE_URL . '/pagina.php';

// Lógica de la página
$datos = obtener_datos($pdo);

// Header
include 'includes/header.php';
?>

<!-- Contenido HTML -->
<main>
    <h1><?= htmlspecialchars($page_title) ?></h1>
    <!-- ... -->
</main>

<?php include 'includes/footer.php'; ?>
```

### 4. Estructura de Página Admin
```php
<?php
require_once 'auth.php'; // Valida sesión automáticamente

$page_title = 'Admin - Título';

// Lógica
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar formulario
}

include 'header.php'; // header admin
?>

<!-- Contenido -->

<?php include 'footer.php'; ?>
```

### 5. Funciones Helper
```php
/**
 * Obtiene proyectos con filtros
 * 
 * @param PDO $pdo Conexión a BD
 * @param array $filtros ['ciclo' => [1,2], 'dificultad' => 'medio']
 * @return array Proyectos encontrados
 */
function get_proyectos($pdo, $filtros = []) {
    console.log('🔍 Obteniendo proyectos con filtros:', $filtros);
    
    $params = [];
    $where = ["p.activo = 1"];
    
    // Filtros dinámicos
    if (!empty($filtros['ciclo'])) {
        $placeholders = implode(',', array_fill(0, count($filtros['ciclo']), '?'));
        $where[] = "p.ciclo IN ($placeholders)";
        $params = array_merge($params, $filtros['ciclo']);
    }
    
    $sql = "SELECT p.* FROM proyectos p WHERE " . implode(' AND ', $where);
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        
        console.log('✅ Proyectos encontrados:', count($result));
        return $result;
    } catch (PDOException $e) {
        console.log('❌ Error en query:', $e->getMessage());
        return [];
    }
}
```

### 6. Escapado de Salida (Prevención XSS)
```php
// ✅ SIEMPRE escapar datos del usuario o BD
<?= htmlspecialchars($proyecto['nombre'], ENT_QUOTES, 'UTF-8') ?>

// ❌ NUNCA imprimir directo
<?= $proyecto['nombre'] ?> <!-- PELIGROSO -->
```

### 7. JavaScript con console.log
```javascript
// Filtros de catálogo
function aplicarFiltros() {
    console.log('🔍 Aplicando filtros...');
    
    const cicloSeleccionado = document.querySelector('input[name="ciclo"]:checked');
    console.log('Ciclo seleccionado:', cicloSeleccionado?.value);
    
    const proyectosFiltrados = filtrarProyectos();
    console.log('✅ Proyectos después de filtrar:', proyectosFiltrados.length);
    
    mostrarProyectos(proyectosFiltrados);
}
```

### 8. AJAX con Fetch
```javascript
async function buscarProyectos(termino) {
    console.log('🔍 Buscando:', termino);
    
    try {
        const response = await fetch('/api/buscar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ busqueda: termino })
        });
        
        console.log('📡 Response status:', response.status);
        
        const data = await response.json();
        console.log('✅ Resultados:', data);
        
        return data;
    } catch (error) {
        console.log('❌ Error en búsqueda:', error);
        return [];
    }
}
```

---

## GUÍA DE DEBUG

### Para el Usuario (Testing en Chrome)

1. **Abrir DevTools**: `F12`
2. **Ir a Console**: Pestaña "Console"
3. **Recargar página**: `Ctrl + R` o `F5`
4. **Ver mensajes**: Buscar los emojis:
   - 🔍 = Punto de debug
   - ✅ = Operación exitosa
   - ❌ = Error encontrado
   - ⚠️ = Advertencia

### Cuando Reportes Errores

Incluye:
```
1. URL de la página
2. Qué acción hiciste (ej: "Hice clic en filtro de Física")
3. Mensaje completo de la consola (captura de pantalla o texto)
4. Mensaje de error PHP (si aparece en pantalla)
```

### Estrategia de console.log

```javascript
// INICIO de función
console.log('🔍 [nombreFuncion] Iniciando con params:', param1, param2);

// ANTES de operación crítica
console.log('🔍 [nombreFuncion] Antes de fetch, URL:', url);

// DESPUÉS de operación exitosa
console.log('✅ [nombreFuncion] Datos recibidos:', data);

// EN CATCH de errores
console.log('❌ [nombreFuncion] Error:', error.message);

// VALORES intermedios importantes
console.log('🔍 [nombreFuncion] Variable X vale:', x);
```

---

## PRIORIDADES DE DESARROLLO (Orden)

### FASE 1: Fundamentos (Actual)
- [ ] `config.php` - Configuración base
- [ ] `includes/db-functions.php` - Funciones de consulta
- [ ] `includes/header.php` y `footer.php`
- [ ] `admin/auth.php` - Sistema de login

### FASE 2: Frontend Público - Core
- [ ] `index.php` - Homepage
- [ ] `catalogo.php` - Lista con filtros
- [ ] `proyecto.php` - Detalle de guía
- [ ] `assets/css/main.css` - Estilos principales
- [ ] `assets/js/catalogo-filtros.js`

### FASE 3: Backend Admin - Proyectos
- [ ] `admin/dashboard.php`
- [ ] `admin/proyectos/index.php` - Lista
- [ ] `admin/proyectos/edit.php` - Editor
- [ ] `admin/guias/edit.php` - Editor de guías

### FASE 4: Asistente IA
- [ ] `api/ia-consulta.php` - Backend IA
- [ ] `assets/js/asistente-ia.js` - Widget frontend

### FASE 5: Backend Admin - CTeI
- [ ] `admin/contratos/` - Módulo completo
- [ ] `admin/entregas/` - Trazabilidad
- [ ] `admin/analytics/` - Reportes

---

## CONVENCIONES DE NOMBRES

### Archivos
- PHP: `kebab-case.php` → `proyecto-edit.php`
- JS: `kebab-case.js` → `catalogo-filtros.js`
- CSS: `kebab-case.css` → `main.css`

### Variables PHP
- `$snake_case` → `$proyectos_activos`

### Variables JavaScript
- `camelCase` → `proyectosFiltrados`

### Clases CSS
- `kebab-case` → `.proyecto-card`, `.btn-primary`

### IDs HTML
- `kebab-case` → `#filtro-ciclo`, `#buscar-proyectos`

### Funciones
- PHP: `snake_case()` → `get_proyectos_por_ciclo()`
- JS: `camelCase()` → `aplicarFiltros()`

### Constantes
- `UPPER_SNAKE_CASE` → `SITE_URL`, `DB_NAME`

---

## SEGURIDAD - CHECKLIST OBLIGATORIO

Antes de entregar código, verifica:

- [ ] **SQL Injection**: ¿Usaste prepared statements?
- [ ] **XSS**: ¿Escapaste con `htmlspecialchars()`?
- [ ] **CSRF**: ¿Incluiste token en formularios admin?
- [ ] **File Upload**: ¿Validaste tipo y tamaño?
- [ ] **Auth**: ¿La página admin requiere `auth.php`?

---

## RESPUESTAS TIPO

### Cuando te piden crear una página:

```markdown
Voy a crear [nombre-pagina.php] que tendrá:

1. **Funcionalidad**: [Descripción breve]
2. **Archivos a crear/editar**:
   - `ruta/archivo.php` - [propósito]
   - `assets/js/script.js` - [propósito]
3. **Queries SQL necesarias**: [describir]
4. **Console.log incluidos**: En [X] puntos para debug

[CÓDIGO AQUÍ]

**Para probar**:
1. Abre http://localhost/clasedeciencia/[ruta]
2. F12 → Console
3. Verifica que aparezcan los mensajes con 🔍
4. [Acción específica a realizar]
```

### Cuando corriges un error:

```markdown
Encontré el problema en [archivo:línea]:

**Causa**: [Explicación]

**Solución**: [Qué cambié]

**Debug adicional**: Agregué console.log() en:
- Línea X: Para ver valor de [variable]
- Línea Y: Para confirmar que entra al if

**Instrucciones**:
1. Recarga la página
2. Abre Console
3. Deberías ver: "🔍 [mensaje esperado]"
4. Si ves "❌ [error]", avísame con el mensaje exacto
```

---

## RECURSOS DE REFERENCIA

### Consultar Antes de Codificar
1. `marco/ANALISIS_Y_PLAN_CLASEDECIENCIA.md` - Especificaciones completas
2. `base_paginas/thegreenalmanac.com/` - Código de referencia
3. `marco/clasedeciencia_requerimientos_v2.txt` - Requerimientos
4. `marco/BASE_DE_DATOS_u626603208_clasedeciencia.md` - Referencia detallada de tablas, campos y relaciones

### Estructura de Base de Datos
- Consultar sección 3 de `ANALISIS_Y_PLAN_CLASEDECIENCIA.md`
- Tablas principales: `proyectos`, `guias`, `materiales`, `contratos`
 - Módulos IA y CTeI: ver `marco/BASE_DE_DATOS_u626603208_clasedeciencia.md`

### Mapeo de Adaptación (thegreenalmanac → clasedeciencia)
- `articles` → `proyectos`
- `sections` → campos `ciclo` y `grados` en `proyectos`
- `tags` → `areas` y `competencias` (dos taxonomías)
- `article_materials` → `proyecto_materiales`
- `materials` / `material_categories` → `materiales` / `categorias_materiales`
- Multimedia → `recursos_multimedia`
- Estadísticas → `analytics_visitas`, `analytics_interacciones`

### Pautas para Queries Comunes
- Catálogo: JOIN `proyectos` + `proyecto_areas` + filtros por `ciclo`, `dificultad`, `duracion_minutos`
- Detalle: cargar `proyectos` por `slug`, guía activa desde `guias`, multimedia desde `recursos_multimedia`
- Materiales del proyecto: usar `proyecto_materiales` (cantidad, notas, `es_incluido_kit`)
- IA: obtener contexto con `sp_obtener_contexto_proyecto` o vista `v_proyecto_contexto_ia`

### Competencias MEN
- Ver Anexo C de `ANALISIS_Y_PLAN_CLASEDECIENCIA.md`
- Competencias de: Indagación, Explicación, Uso del Conocimiento

---

## PREGUNTAS FRECUENTES

**P: ¿Puedo usar jQuery?**
R: Preferible Vanilla JS. Si es imprescindible, avisa primero.

**P: ¿Cómo manejo imágenes?**
R: Upload a `uploads/proyectos/` o `uploads/materiales/`. Validar tipo (jpg, png, webp) y tamaño (<5MB).

**P: ¿Necesito un helper para [X]?**
R: Si ya existe similar en `thegreenalmanac.com/includes/functions.php`, reutilízalo. Si no, créalo.

**P: ¿El usuario reporta "Pantalla en blanco"?**
R: Probablemente error PHP. Pide que active `display_errors = 1` en `config.php` temporalmente.

**P: ¿Debo crear el SQL de la tabla?**
R: NO ejecutes SQL. SÍ puedes escribirlo en comentarios del código:
```php
/**
 * TABLA REQUERIDA:
 * 
 * CREATE TABLE proyectos (
 *   id INT PRIMARY KEY AUTO_INCREMENT,
 *   nombre VARCHAR(255),
 *   ...
 * );
 */
```

**P: ¿Qué hago con las APIs externas (IA)?**
R: Crea el endpoint con un TODO:
```php
// TODO: Configurar API key de OpenAI en config.php
// define('OPENAI_API_KEY', 'sk-...');
```

---

## PLANTILLA DE CÓDIGO INICIAL

Cuando crees una página nueva, usa esta estructura:

```php
<?php
/**
 * [Nombre de la Página]
 * 
 * Descripción: [Qué hace esta página]
 * Ruta: /ruta/archivo.php
 * Requiere: [Tablas de BD necesarias]
 * 
 * @package ClaseDeCiencia
 * @author [Tu nombre]
 * @date [Fecha]
 */

// Configuración e includes
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db-functions.php';

// Si es admin, validar sesión
// require_once 'auth.php';

// Variables para header
$page_title = 'Título de la Página';
$page_description = 'Descripción SEO de la página';
$canonical_url = SITE_URL . '/ruta/archivo.php';

// Lógica principal
try {
    // Procesar formularios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // console.log en el siguiente script JS
    }
    
    // Obtener datos
    $datos = funcion_helper($pdo);
    
} catch (Exception $e) {
    error_log('Error en [archivo.php]: ' . $e->getMessage());
    // Mostrar mensaje amigable al usuario
}

// Incluir header
include '../includes/header.php';
?>

<!-- Contenido HTML -->
<main class="container">
    <h1><?= htmlspecialchars($page_title) ?></h1>
    
    <!-- Contenido aquí -->
    
</main>

<script>
// JavaScript con console.log para debug
console.log('🔍 [NombrePagina] Página cargada');

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ [NombrePagina] DOM listo');
    
    // Código JavaScript aquí
});
</script>

<?php include '../includes/footer.php'; ?>
```

---

## RECORDATORIOS FINALES

### Siempre:
✅ Agrega `console.log()` generosamente  
✅ Comenta código complejo  
✅ Usa prepared statements  
✅ Escapa output con `htmlspecialchars()`  
✅ Sigue estructura existente  

### Nunca:
❌ Ejecutes o pruebes código  
❌ Toques la base de datos directamente  
❌ Instales dependencias  
❌ Asumas que algo funciona sin console.log  
❌ Dejes SQL queries concatenados directamente  

---

**Versión**: 1.0  
**Última actualización**: 19 Diciembre 2025  
**Proyecto**: clasedeciencia.com  
**Mantener este documento actualizado con cambios importantes en el proyecto**
