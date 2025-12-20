# Clase de Ciencia - AI Coding Agent Instructions

## Critical Development Rules

### 🚨 Agent Permissions (MANDATORY)
- **CODE ONLY**: Write PHP, HTML, CSS, JavaScript files
- **NO EXECUTION**: Never run code, test in browser, or start servers
- **NO DATABASE**: Never create tables, run queries, or modify DB (user handles via SQL)
- **NO INSTALLS**: Never use npm/composer - user manages dependencies
- **DEBUG VIA CONSOLE**: Use `console.log()` extensively with emojis: 🔍 (debug), ✅ (success), ❌ (error), ⚠️ (warning)

### 📋 User Testing Workflow
1. User opens Chrome DevTools (F12)
2. User checks Console tab for messages
3. User reports errors with full console output
4. Agent adds more `console.log()` for diagnosis
5. Agent fixes code based on user feedback

## Project Architecture

### Big Picture
**clasedeciencia.com** is an educational platform for Colombian students (grades 6°-11°) featuring scientific projects with interactive guides and AI assistance. It combines:

- **Structure from** `base_paginas/thegreenalmanac.com/` (PDO/MySQL admin backend)
- **Content model from** `base_paginas/freescienceproject.com/` (science projects catalog)
- **Specifications in** `marco/ANALISIS_Y_PLAN_CLASEDECIENCIA.md` (full architecture)

**Core Philosophy**: No user registration/login - physical kit ownership is the access key.

### Directory Structure
```
clasedeciencia.com/
├── config.php                 # DB config (user sets credentials)
├── index.php                  # Homepage
├── catalogo.php               # Project catalog with filters
├── proyecto.php?slug=         # Interactive project guide
├── includes/
│   ├── header.php             # Site header with SEO
│   ├── footer.php             # Site footer
│   ├── db-functions.php       # PDO query helpers
│   └── functions.php          # Utilities
├── admin/                     # Backend admin panel
│   ├── auth.php               # Session-based login
│   ├── dashboard.php          # Admin home
│   ├── proyectos/             # Project CRUD
│   ├── contratos/             # CTeI contract management
│   └── entregas/              # Kit traceability
├── assets/
│   ├── css/main.css           # Responsive mobile-first
│   ├── js/catalogo-filtros.js # Filter interactions
│   └── js/asistente-ia.js     # AI assistant widget
└── api/
    ├── buscar.php             # Search endpoint
    └── ia-consulta.php        # AI proxy with safety guardrails
```

## Database Architecture

### Core Tables (User Creates via SQL)
```php
// Projects: Main content entity
proyectos: id, nombre, slug, ciclo(1|2|3), grados[], areas[], dificultad, duracion_minutos

// Guides: Step-by-step instructions
guias: proyecto_id, pasos[JSON], explicacion_cientifica, competencias_men[]

// Materials: Kit components
materiales: nombre_comun, categoria_id, advertencias_seguridad

// Admin: CTeI contract tracking
contratos: numero, entidad_contratante, departamento, valor
entregas: contrato_id, institucion_educativa, fecha, acta_pdf

// Analytics: Anonymous usage tracking
analytics_visitas: proyecto_id, tipo_pagina, departamento, dispositivo
```

### Query Pattern (ALWAYS Use Prepared Statements)
```php
// ✅ CORRECT - Prevents SQL injection
$stmt = $pdo->prepare("SELECT * FROM proyectos WHERE ciclo = ? AND activo = ?");
$stmt->execute([$ciclo, 1]);
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ❌ WRONG - Vulnerable
$result = $pdo->query("SELECT * FROM proyectos WHERE ciclo = $ciclo");
```

## Critical Coding Patterns

### Page Structure (Public)
```php
<?php
require_once 'config.php';
require_once 'includes/db-functions.php';

$page_title = 'Título SEO';
$page_description = 'Meta description';
$canonical_url = SITE_URL . '/pagina.php';

// Logic with try-catch
try {
    $datos = get_data($pdo);
} catch (PDOException $e) {
    error_log('Error: ' . $e->getMessage());
    $datos = [];
}

include 'includes/header.php';
?>
<main>
    <h1><?= htmlspecialchars($page_title) ?></h1>
    <!-- Content - ALWAYS escape output -->
    <?php foreach ($datos as $item): ?>
        <p><?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endforeach; ?>
</main>
<script>
console.log('🔍 [PageName] Loaded with', <?= count($datos) ?>, 'items');
</script>
<?php include 'includes/footer.php'; ?>
```

### Admin Page Structure
```php
<?php
require_once 'auth.php'; // Auto-validates session
$page_title = 'Admin - Module';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    console.log('🔍 [Admin] Processing form...');
    // CSRF token validation
    // Process with prepared statements
}
include 'header.php'; // Admin header
?>
<!-- Admin content -->
<?php include 'footer.php'; ?>
```

### JavaScript with Debug Logging
```javascript
async function buscarProyectos(termino) {
    console.log('🔍 [buscarProyectos] Iniciando búsqueda:', termino);
    
    try {
        const response = await fetch('/api/buscar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ busqueda: termino })
        });
        
        console.log('📡 [buscarProyectos] Status:', response.status);
        const data = await response.json();
        console.log('✅ [buscarProyectos] Resultados:', data.length);
        
        return data;
    } catch (error) {
        console.log('❌ [buscarProyectos] Error:', error.message);
        return [];
    }
}
```

## Security Checklist (Verify Before Committing)

- [ ] SQL: Used `$pdo->prepare()` with `?` placeholders
- [ ] XSS: Escaped all output with `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`
- [ ] CSRF: Admin forms include token validation
- [ ] File Upload: Validated type, size, extension (`jpg|png|webp`, <5MB)
- [ ] Auth: Admin pages start with `require_once 'auth.php'`

## Project-Specific Conventions

### Naming Conventions
- **Files**: `kebab-case.php` → `proyecto-edit.php`
- **PHP Variables**: `$snake_case` → `$proyectos_activos`
- **JS Variables**: `camelCase` → `proyectosFiltrados`
- **CSS Classes**: `kebab-case` → `.proyecto-card`, `.btn-primary`
- **Functions PHP**: `snake_case()` → `get_proyectos_por_ciclo()`
- **Functions JS**: `camelCase()` → `aplicarFiltros()`

### Ciclo System (Colombian Education Grades)
```php
// Ciclo 1: Exploración (6°-7° grado) - Observar, describir
// Ciclo 2: Experimentación (8°-9° grado) - Explicar, comparar
// Ciclo 3: Análisis (10°-11° grado) - Analizar, argumentar
```

### Areas (Subject Tags)
```php
$areas = ['Física', 'Química', 'Biología', 'Tecnología', 'Ambiental'];
```

### Competencias MEN (Colombian Education Standards)
```php
// Always link projects to MEN competencies:
$competencias = [
    'indagacion' => 'Formulo preguntas, observo, registro datos',
    'explicacion' => 'Establezco relaciones causales, modelo fenómenos',
    'uso_conocimiento' => 'Aplico conceptos a situaciones reales'
];
```

## AI Assistant Integration

### Safety Guardrails (api/ia-consulta.php)
```php
// ALWAYS validate AI responses for safety
function validar_respuesta($respuesta) {
    $palabras_peligro = ['fuego', 'explosión', 'ácido fuerte'];
    // If detected dangerous modification:
    return "⚠️ Consulta con tu profesor antes de modificar el experimento.";
}
```

### Context Injection Pattern
```javascript
// Send project context to AI
const contexto = {
    proyecto_id: <?= $proyecto['id'] ?>,
    nombre: "<?= $proyecto['nombre'] ?>",
    materiales: <?= json_encode($materiales) ?>,
    conceptos: <?= json_encode($conceptos_clave) ?>
};

fetch('/api/ia-consulta.php', {
    method: 'POST',
    body: JSON.stringify({ contexto, pregunta: userQuestion })
});
```

## Reference Files (Read Before Coding)

- **`marco/ANALISIS_Y_PLAN_CLASEDECIENCIA.md`** - Full architecture specs
- **`marco/CHAT_INSTRUCTIONS.md`** - Detailed coding patterns
- **`marco/clasedeciencia_requerimientos_v2.txt`** - Requirements
- **`base_paginas/thegreenalmanac.com/`** - Reference for admin backend
- **`base_paginas/freescienceproject.com/`** - Reference for project structure

## Common Workflows

### Creating a New Page
1. Read similar page in `thegreenalmanac.com/` for pattern
2. Define SEO variables before header include
3. Write logic with try-catch around DB queries
4. Add strategic `console.log()` points
5. Include header/footer
6. Test by asking user to open in browser and check console

### Fixing Reported Error
1. Ask user: "What's the exact console message?"
2. Locate error line in code
3. Add `console.log()` before/after problem area
4. Fix the issue
5. Explain: "Now you should see '✅ [Function] Success' in console"

### Adding a Filter
1. Update SQL in `includes/db-functions.php` with new WHERE clause
2. Add checkbox/select in catalog sidebar
3. Add JS event listener with console logging
4. Test filter logic via user feedback

## Documentation Policy
**DO NOT** create `*.md` summary files after changes unless explicitly requested. Focus on code changes only.

---

**Version**: 1.0  
**Last Updated**: December 19, 2025  
**Read More**: `marco/CHAT_INSTRUCTIONS.md` for exhaustive patterns
