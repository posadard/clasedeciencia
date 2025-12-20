# ANÁLISIS Y PLAN DE DESARROLLO - clasedeciencia.com

## Fecha: 19 de Diciembre 2025

---

## 1. CONTEXTO Y OBJETIVO

### Objetivo del Proyecto
Crear **clasedeciencia.com** utilizando:
- **ESTRUCTURA** de `thegreenalmanac.com` (arquitectura backend/frontend, base de datos, admin)
- **CONTENIDO** similar a `freescienceproject.com` (proyectos científicos para estudiantes)
- **REQUERIMIENTOS** definidos en `clasedeciencia_requerimientos_v2.txt`

### Filosofía Central del Sistema
> **El kit físico es la llave**. Quien posea un kit puede entrar a la plataforma y aprender. NO se requiere registro, login ni recolección de datos personales. La plataforma es pública y abierta.

---

## 2. ANÁLISIS DE PROYECTOS EXISTENTES

### 2.1 THE GREEN ALMANAC (thegreenalmanac.com)
**Propósito**: Revista online de química práctica para agricultores y homesteaders

#### ✅ Arquitectura a Reutilizar:

**ESTRUCTURA DE BASE DE DATOS**
- ✅ `articles` → **proyectos** (adaptado)
- ✅ `sections` → **ciclos/grados** (1: 6°-7°, 2: 8°-9°, 3: 10°-11°)
- ✅ `tags` → **áreas/competencias** (Física, Química, Biología, etc.)
- ✅ `materials` → **materiales de kits**
- ✅ `article_materials` → **proyecto_materiales**
- ✅ `material_categories` → **categorías de materiales**
- ✅ Sistema de clicks tracking → **analytics anónimos**

**BACKEND ADMINISTRATIVO**
```
admin/
├── index.php          → Login
├── dashboard.php      → Dashboard principal
├── articles.php       → CRUD de proyectos
├── article-edit.php   → Editor de proyectos
├── sections.php       → Gestión de ciclos
├── tags.php           → Gestión de áreas/competencias
├── materials.php      → Gestión de materiales
└── material-stats.php → Analytics
```

**FUNCIONALIDADES CLAVE**
- ✅ Sistema de autenticación simple (session-based)
- ✅ CRUD completo con interfaz limpia
- ✅ PDO + MySQL con prepared statements
- ✅ Schema.org markup dinámico
- ✅ Sistema de filtros múltiples
- ✅ Responsive y optimizado para móviles
- ✅ SEO optimizado (canonical URLs, meta tags)

**FRONTEND PÚBLICO**
```
/                    → Homepage con featured projects
/library.php         → Catálogo con filtros
/article.php?slug=   → Detalle de proyecto (GUÍA INTERACTIVA)
/section.php?slug=   → Proyectos por ciclo
/material.php?slug=  → Detalle de material
/search.php          → Búsqueda
```

#### 🎨 Características de Diseño:
- Minimalista, rápido, bajo ancho de banda
- Grid cards responsivo
- Navegación por filtros (sección, tags, dificultad, formato)
- Integración de e-commerce externo (ChemicalStore)

---

### 2.2 FREE SCIENCE PROJECT (freescienceproject.com)
**Propósito**: Catálogo de 220+ proyectos científicos K-12

#### ✅ Contenido a Adaptar:

**ORGANIZACIÓN DE PROYECTOS**
```
Primary (K-4)       → No aplica para Clase de Ciencia
Elementary (4-6)    → Ciclo 1: 6°-7° (Exploración)
Intermediate (7-8)  → Ciclo 2: 8°-9° (Experimentación)
Senior (9-12)       → Ciclo 3: 10°-11° (Análisis)
```

**ESTRUCTURA DE PROYECTOS INDIVIDUALES**
Cada proyecto tiene:
- ✅ Título y descripción
- ✅ Grado recomendado
- ✅ Área (Physics, Chemistry, Biology, etc.)
- ✅ Dificultad (Easy, Medium, Hard, Advanced)
- ✅ Materiales necesarios
- ✅ Pasos detallados con imágenes
- ✅ Información científica
- ✅ Links a kits comerciales

**PROYECTOS ALINEADOS CON CLASE DE CIENCIA**
Del portafolio propuesto en `Clase_de_Ciencia_Propuesta_CTeI_v3.txt`:

| Proyecto CdC | Proyecto FSP Existente | Ciclo |
|--------------|------------------------|-------|
| Microscopio sencillo | (No existe - crear) | 1 |
| Pulmón mecánico | (No existe - crear) | 1 |
| Circuito eléctrico básico | Make A Simple Electric Circuit | 1 |
| Separación de mezclas | (No existe - crear) | 1 |
| Test de pH | Most Liquids contain Acid or Alkali | 1 |
| Radio de cristal | Crystal Radio | 2 |
| Motor eléctrico simple | Parts of an Electric Motor / electromotor | 2 |
| Osmosis con vegetales | (No existe - crear) | 2 |
| Carro trampa de ratón | (No existe - crear) | 2 |
| Generador manual (dinamo) | How Electricity is Made | 2 |
| Carro solar | Solar Science | 3 |
| Turbina eólica | (No existe - crear) | 3 |
| Electroimán | Electromagnet Experiments | 3 |
| Tratamiento de agua | Distillation of Water | 3 |
| Análisis químico | A Chemical Change | 3 |

**CARACTERÍSTICAS DE UI**
- ✅ Filtros en panel lateral fijo
- ✅ Búsqueda instantánea con JavaScript
- ✅ Cards con badges (dificultad, grado, área)
- ✅ Sistema de "Popular projects"
- ✅ Print-friendly layouts

---

## 3. MODELO DE DATOS PARA CLASE DE CIENCIA

### 3.1 Tablas del Frontend Público

#### TABLA: `proyectos`
```sql
CREATE TABLE proyectos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  
  -- Clasificación
  ciclo ENUM('1','2','3') NOT NULL, -- 1:6°-7°, 2:8°-9°, 3:10°-11°
  grados JSON NOT NULL, -- [6,7] o [8,9] o [10,11]
  
  -- Metadata
  areas JSON NOT NULL, -- ["Física","Química","Biología","Tecnología","Ambiental"]
  duracion_minutos INT DEFAULT 60,
  dificultad ENUM('facil','medio','dificil') DEFAULT 'medio',
  
  -- Contenido público
  resumen TEXT,
  objetivo_aprendizaje TEXT,
  imagen_portada VARCHAR(255),
  video_portada VARCHAR(255),
  
  -- Seguridad
  seguridad JSON, -- {edad_min:11, requiere_supervision:true, advertencias:[]}
  
  -- SEO
  seo_title VARCHAR(255),
  seo_description TEXT,
  canonical_url VARCHAR(255),
  
  -- Control
  activo BOOLEAN DEFAULT TRUE,
  destacado BOOLEAN DEFAULT FALSE,
  orden_popularidad INT DEFAULT 0,
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_ciclo (ciclo),
  INDEX idx_activo (activo),
  INDEX idx_destacado (destacado)
);
```

#### TABLA: `guias`
```sql
CREATE TABLE guias (
  id INT PRIMARY KEY AUTO_INCREMENT,
  proyecto_id INT NOT NULL,
  version VARCHAR(20) DEFAULT '1.0',
  
  -- Contenido de la guía
  introduccion TEXT,
  materiales_kit JSON, -- [{nombre:"",cantidad:"",descripcion:""}]
  materiales_adicionales JSON,
  seccion_seguridad TEXT,
  pasos JSON, -- [{numero:1, titulo:"", descripcion:"", imagen:"", video:""}]
  explicacion_cientifica TEXT,
  conceptos_clave JSON, -- ["Corriente eléctrica","Circuito","Energía"]
  conexiones_realidad TEXT,
  para_profundizar TEXT,
  
  -- Alineación MEN
  competencias_men JSON, -- ["Indagación","Explicación de fenómenos"]
  dba_relacionados JSON,
  estandares_men JSON,
  
  -- Control de versión
  activa BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
  INDEX idx_proyecto_activa (proyecto_id, activa)
);
```

#### TABLA: `recursos_multimedia`
```sql
CREATE TABLE recursos_multimedia (
  id INT PRIMARY KEY AUTO_INCREMENT,
  proyecto_id INT NOT NULL,
  tipo ENUM('imagen','video','simulacion','pdf') NOT NULL,
  titulo VARCHAR(255),
  descripcion TEXT,
  url VARCHAR(500) NOT NULL,
  orden INT DEFAULT 0,
  
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
  INDEX idx_proyecto (proyecto_id)
);
```

#### TABLA: `materiales`
```sql
CREATE TABLE materiales (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre_comun VARCHAR(255) NOT NULL,
  nombre_tecnico VARCHAR(255),
  descripcion TEXT,
  slug VARCHAR(255) UNIQUE NOT NULL,
  categoria_id INT,
  imagen VARCHAR(255),
  
  -- Información de seguridad
  advertencias_seguridad TEXT,
  manejo_recomendado TEXT,
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (categoria_id) REFERENCES categorias_materiales(id),
  INDEX idx_categoria (categoria_id)
);
```

#### TABLA: `proyecto_materiales`
```sql
CREATE TABLE proyecto_materiales (
  proyecto_id INT NOT NULL,
  material_id INT NOT NULL,
  cantidad VARCHAR(50), -- "1 unidad", "10 cm", "500 ml"
  es_incluido_kit BOOLEAN DEFAULT TRUE,
  notas TEXT,
  
  PRIMARY KEY (proyecto_id, material_id),
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
  FOREIGN KEY (material_id) REFERENCES materiales(id) ON DELETE CASCADE
);
```

#### TABLA: `categorias_materiales`
```sql
CREATE TABLE categorias_materiales (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  slug VARCHAR(100) UNIQUE NOT NULL,
  icono VARCHAR(50), -- emoji o clase CSS
  descripcion TEXT
);
```

#### TABLA: `areas`
```sql
CREATE TABLE areas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL, -- "Física", "Química", "Biología"
  slug VARCHAR(100) UNIQUE NOT NULL,
  color VARCHAR(7), -- código hex para badges
  descripcion TEXT
);
```

#### TABLA: `proyecto_areas`
```sql
CREATE TABLE proyecto_areas (
  proyecto_id INT NOT NULL,
  area_id INT NOT NULL,
  
  PRIMARY KEY (proyecto_id, area_id),
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
  FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE
);
```

#### TABLA: `competencias`
```sql
CREATE TABLE competencias (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(255) NOT NULL,
  descripcion TEXT,
  tipo ENUM('indagacion','explicacion','uso_conocimiento') NOT NULL
);
```

#### TABLA: `proyecto_competencias`
```sql
CREATE TABLE proyecto_competencias (
  proyecto_id INT NOT NULL,
  competencia_id INT NOT NULL,
  
  PRIMARY KEY (proyecto_id, competencia_id),
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
  FOREIGN KEY (competencia_id) REFERENCES competencias(id) ON DELETE CASCADE
);
```

---

### 3.2 Tablas del Backend Administrativo (Gestión CTeI)

#### TABLA: `contratos`
```sql
CREATE TABLE contratos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  numero_contrato VARCHAR(100) NOT NULL,
  
  -- Entidad contratante
  entidad_contratante VARCHAR(255) NOT NULL,
  departamento VARCHAR(100) NOT NULL,
  municipios_alcance JSON, -- ["Bogotá","Soacha","Zipaquirá"]
  
  -- Fechas y valores
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  valor_contrato DECIMAL(15,2),
  
  -- Detalles
  objeto_contrato TEXT,
  supervisor VARCHAR(255),
  
  -- Alcance
  ie_beneficiarias INT,
  estudiantes_estimados INT,
  docentes_estimados INT,
  ciclos_incluidos JSON, -- [1,2,3]
  grados_incluidos JSON, -- [6,7,8,9,10,11]
  
  -- Estado
  estado ENUM('borrador','activo','ejecucion','finalizado') DEFAULT 'borrador',
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### TABLA: `contrato_proyectos`
```sql
CREATE TABLE contrato_proyectos (
  contrato_id INT NOT NULL,
  proyecto_id INT NOT NULL,
  cantidad_kits INT NOT NULL,
  
  PRIMARY KEY (contrato_id, proyecto_id),
  FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id)
);
```

#### TABLA: `justificacion_ctei`
```sql
CREATE TABLE justificacion_ctei (
  contrato_id INT PRIMARY KEY,
  
  -- Justificación técnica
  justificacion_ctei TEXT,
  actividades_decreto_591 JSON, -- [4,5,6,7] numerales del Decreto
  alineacion_ley_1286 TEXT,
  
  -- Metodología
  competencias_men_globales JSON,
  metodologia_pedagogica TEXT,
  componente_innovacion TEXT, -- Descripción del componente IA
  
  -- Indicadores
  indicadores_propuestos JSON,
  metas_propuestas JSON,
  
  FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE
);
```

#### TABLA: `lotes_kits`
```sql
CREATE TABLE lotes_kits (
  id INT PRIMARY KEY AUTO_INCREMENT,
  codigo_lote VARCHAR(100) UNIQUE NOT NULL,
  proyecto_id INT NOT NULL,
  contrato_id INT NOT NULL,
  
  cantidad INT NOT NULL,
  fecha_produccion DATE,
  estado ENUM('producido','bodega','despachado','entregado') DEFAULT 'producido',
  
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
  FOREIGN KEY (contrato_id) REFERENCES contratos(id),
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### TABLA: `entregas`
```sql
CREATE TABLE entregas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  contrato_id INT NOT NULL,
  
  -- Institución Educativa
  institucion_educativa VARCHAR(255) NOT NULL,
  codigo_dane VARCHAR(50),
  municipio VARCHAR(100) NOT NULL,
  direccion TEXT,
  
  -- Entrega
  fecha_entrega DATETIME NOT NULL,
  responsable_entrega VARCHAR(255), -- Quien entrega (CDC)
  responsable_recepcion VARCHAR(255), -- Quien recibe (IE)
  cargo_recepcion VARCHAR(255),
  
  -- Observaciones
  observaciones TEXT,
  evidencia_fotografica JSON, -- URLs de fotos
  firma_digital VARCHAR(255), -- URL de firma
  acta_generada VARCHAR(255), -- URL del PDF
  
  FOREIGN KEY (contrato_id) REFERENCES contratos(id),
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### TABLA: `entrega_lotes`
```sql
CREATE TABLE entrega_lotes (
  entrega_id INT NOT NULL,
  lote_id INT NOT NULL,
  cantidad_entregada INT NOT NULL,
  
  PRIMARY KEY (entrega_id, lote_id),
  FOREIGN KEY (entrega_id) REFERENCES entregas(id) ON DELETE CASCADE,
  FOREIGN KEY (lote_id) REFERENCES lotes_kits(id)
);
```

---

### 3.3 Tablas de Analytics (Anónimo)

#### TABLA: `analytics_visitas`
```sql
CREATE TABLE analytics_visitas (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  
  -- Qué
  proyecto_id INT,
  tipo_pagina ENUM('home','catalogo','proyecto','material','busqueda'),
  url_visitada VARCHAR(500),
  
  -- Cuándo
  fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  -- Dónde (aproximado por IP)
  pais VARCHAR(100),
  departamento VARCHAR(100),
  ciudad VARCHAR(100),
  
  -- Cómo
  dispositivo ENUM('mobile','tablet','desktop'),
  navegador VARCHAR(100),
  
  -- Sesión anónima
  sesion_hash VARCHAR(64), -- Hash del IP + User Agent
  
  INDEX idx_proyecto (proyecto_id),
  INDEX idx_fecha (fecha_hora),
  INDEX idx_departamento (departamento)
);
```

#### TABLA: `analytics_interacciones`
```sql
CREATE TABLE analytics_interacciones (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  proyecto_id INT,
  
  tipo_interaccion ENUM('descarga_pdf','consulta_ia','click_material','compartir'),
  detalles JSON,
  
  fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sesion_hash VARCHAR(64),
  
  INDEX idx_proyecto (proyecto_id),
  INDEX idx_tipo (tipo_interaccion)
);
```

---

## 4. ARQUITECTURA DE LA APLICACIÓN

### 4.1 Estructura de Carpetas Propuesta

```
clasedeciencia.com/
│
├── config.php                    # Configuración DB y constantes
├── index.php                     # Homepage
├── .htaccess                     # Rewrite rules
├── robots.txt
├── sitemap.xml.php              # Sitemap dinámico
│
├── includes/                     # Componentes compartidos
│   ├── header.php
│   ├── footer.php
│   ├── db-functions.php         # Funciones de consulta
│   ├── functions.php            # Utilidades generales
│   └── proyecto-helpers.php     # Helpers específicos
│
├── admin/                        # Backend administrativo
│   ├── index.php                # Login
│   ├── auth.php                 # Autenticación
│   ├── dashboard.php            # Dashboard principal
│   ├── header.php / footer.php
│   │
│   ├── proyectos/               # Gestión de proyectos
│   │   ├── index.php            # Lista de proyectos
│   │   ├── edit.php             # Editor WYSIWYG
│   │   ├── delete.php
│   │   └── preview.php
│   │
│   ├── guias/                   # Gestión de guías
│   │   ├── index.php
│   │   └── edit.php
│   │
│   ├── materiales/              # Gestión de materiales
│   │   ├── index.php
│   │   ├── edit.php
│   │   └── categorias.php
│   │
│   ├── contratos/               # Gestión contractual CTeI
│   │   ├── index.php            # Lista de contratos
│   │   ├── edit.php             # Editor de contrato
│   │   ├── alcance.php          # Define proyectos incluidos
│   │   ├── justificacion.php    # Justificación CTeI
│   │   └── exportar/
│   │       ├── ficha-tecnica.php
│   │       ├── portafolio.php
│   │       └── matriz-competencias.php
│   │
│   ├── entregas/                # Trazabilidad de kits
│   │   ├── index.php            # Lista de entregas
│   │   ├── registrar.php        # Nueva entrega
│   │   ├── lotes.php            # Gestión de lotes
│   │   └── acta-pdf.php         # Genera acta en PDF
│   │
│   └── analytics/               # Analytics del sitio
│       ├── dashboard.php        # Vista general
│       ├── por-proyecto.php     # Stats por proyecto
│       └── geografico.php       # Mapa de accesos
│
├── catalogo.php                 # Catálogo completo con filtros
├── proyecto.php?slug=           # Detalle del proyecto (GUÍA)
├── ciclo.php?id=                # Proyectos por ciclo
├── area.php?slug=               # Proyectos por área
├── material.php?slug=           # Detalle de material
├── buscar.php                   # Búsqueda
│
├── seguridad.php                # Información de seguridad
├── sobre-el-proyecto.php        # Sobre Clase de Ciencia
├── contacto.php
├── privacidad.php
├── terminos.php
│
├── api/                         # APIs internas
│   ├── buscar.php               # Búsqueda JSON
│   ├── analytics.php            # Log de visitas
│   └── ia-consulta.php          # Proxy para asistente IA
│
├── assets/
│   ├── css/
│   │   ├── main.css
│   │   ├── admin.css
│   │   └── print.css
│   ├── js/
│   │   ├── catalogo-filtros.js
│   │   ├── proyecto.js
│   │   ├── asistente-ia.js
│   │   └── analytics.js
│   └── img/
│       ├── proyectos/
│       ├── materiales/
│       └── logos/
│
└── uploads/                     # Contenido subido
    ├── proyectos/
    ├── guias/
    ├── materiales/
    └── entregas/
```

---

### 4.2 Flujo de Navegación del Usuario

#### RUTA 1: Entrada Directa (Sin Kit - Exploración)
```
1. Usuario → clasedeciencia.com
2. Ve homepage con proyectos destacados
3. Navega a catálogo.php
4. Aplica filtros (ciclo, área, dificultad)
5. Hace click en proyecto
6. Lee la guía interactiva completa
7. Puede consultar al asistente IA
8. Descarga PDF de la guía
```

#### RUTA 2: Con Kit (Ejecución del Proyecto)
```
1. Estudiante recibe kit en IE
2. Accede a clasedeciencia.com desde móvil/tablet
3. Busca el proyecto por nombre o explora catálogo
4. Abre la guía del proyecto
5. Sigue los pasos mientras ejecuta físicamente
6. Consulta dudas al asistente IA
7. Completa el proyecto
8. Lee "Para Profundizar" y recursos adicionales
```

#### RUTA 3: Docente Planificando Clase
```
1. Docente → catálogo.php
2. Filtra por ciclo específico (ej: Ciclo 2 - 8° grado)
3. Revisa competencias MEN de cada proyecto
4. Descarga múltiples guías en PDF
5. Prepara la sesión de clase
6. Durante clase, proyecta la guía en pantalla
7. Estudiantes siguen en sus dispositivos
```

---

## 5. PÁGINAS PÚBLICAS - ESPECIFICACIONES DETALLADAS

### 5.1 Homepage (index.php)

**Elementos**:
- Hero section con buscador destacado
- Proyectos destacados (featured = true)
- Navegación rápida por ciclos (3 cards: Ciclo 1, 2, 3)
- Navegación por áreas (badges: Física, Química, Biología, etc.)
- CTA a catálogo completo
- Sección "¿Qué es Clase de Ciencia?" (breve)

**SQL Query**:
```php
$proyectos_destacados = get_proyectos($pdo, ['destacado' => true, 'limit' => 6]);
$ciclos = get_ciclos_con_conteo($pdo);
$areas = get_areas_con_conteo($pdo);
```

---

### 5.2 Catálogo (catalogo.php)

**Panel de Filtros (Sidebar Fijo)**:
- Búsqueda por texto
- Ciclo (checkboxes: 1, 2, 3)
- Áreas (checkboxes: Física, Química, Biología, Tecnología, Ambiental)
- Dificultad (checkboxes: Fácil, Medio, Difícil)
- Duración (rangos: <30min, 30-60min, 60-90min, >90min)
- Materiales (checkboxes: Solo incluidos en kit, Requiere materiales adicionales)

**Área de Resultados**:
- Grid responsivo de cards (3 columnas desktop, 2 tablet, 1 móvil)
- Cada card muestra:
  - Imagen del proyecto
  - Título
  - Badges: Ciclo, Área, Dificultad
  - Duración
  - Resumen corto
  - CTA: "Ver Guía"

**Ordenamiento**:
- Por defecto: Popularidad (campo `orden_popularidad`)
- Opciones: Alfabético, Duración, Más recientes

**SQL Base** (con filtros dinámicos):
```php
function get_proyectos($pdo, $filtros = []) {
    $params = [];
    $where = ["p.activo = 1"];
    $joins = [];
    
    // Filtro por ciclo
    if (!empty($filtros['ciclo'])) {
        $where[] = "p.ciclo IN (" . implode(',', array_fill(0, count($filtros['ciclo']), '?')) . ")";
        $params = array_merge($params, $filtros['ciclo']);
    }
    
    // Filtro por área
    if (!empty($filtros['areas'])) {
        $joins[] = "INNER JOIN proyecto_areas pa ON p.id = pa.proyecto_id";
        $joins[] = "INNER JOIN areas a ON pa.area_id = a.id";
        $where[] = "a.slug IN (" . implode(',', array_fill(0, count($filtros['areas']), '?')) . ")";
        $params = array_merge($params, $filtros['areas']);
    }
    
    // Filtro por dificultad
    if (!empty($filtros['dificultad'])) {
        $where[] = "p.dificultad IN (" . implode(',', array_fill(0, count($filtros['dificultad']), '?')) . ")";
        $params = array_merge($params, $filtros['dificultad']);
    }
    
    // Búsqueda por texto
    if (!empty($filtros['busqueda'])) {
        $where[] = "(p.nombre LIKE ? OR p.resumen LIKE ?)";
        $busqueda = '%' . $filtros['busqueda'] . '%';
        $params[] = $busqueda;
        $params[] = $busqueda;
    }
    
    $sql = "SELECT DISTINCT p.* FROM proyectos p " . 
           implode(' ', $joins) . " WHERE " . 
           implode(' AND ', $where) . 
           " ORDER BY p.orden_popularidad DESC, p.nombre ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
```

---

### 5.3 Guía del Proyecto (proyecto.php?slug=)

**Estructura de la Página**:

#### SECCIÓN 1: Header del Proyecto
- Título del proyecto
- Badges: Ciclo, Grado(s), Área(s), Dificultad, Duración
- Imagen/video portada
- Botón "Descargar PDF"
- Botón "Compartir"

#### SECCIÓN 2: Introducción
- ¿Qué vamos a hacer?
- ¿Por qué es interesante?
- ¿Qué vamos a aprender? (objetivo de aprendizaje)

#### SECCIÓN 3: Seguridad ⚠️
- Edad recomendada
- Requiere supervisión adulta: Sí/No
- Lista de advertencias destacadas
- Checklist de preparación

#### SECCIÓN 4: Materiales
Dos subsecciones:
1. **Incluidos en el kit**: Lista con checkboxes visuales
2. **Materiales adicionales**: Lista de lo que debe conseguir el usuario

Cada material es clickeable → va a material.php?slug=

#### SECCIÓN 5: Pasos de Ejecución
Lista numerada con:
- Título del paso
- Descripción detallada
- Imagen/diagrama (si aplica)
- Video corto (si aplica)
- Tips destacados (si aplica)

Formato:
```html
<div class="paso" id="paso-1">
  <div class="paso-numero">1</div>
  <div class="paso-contenido">
    <h3>Título del Paso</h3>
    <p>Descripción...</p>
    <img src="paso1.jpg" alt="...">
    <div class="paso-tip">💡 Tip: ...</div>
  </div>
</div>
```

#### SECCIÓN 6: Explicación Científica
- ¿Por qué funciona?
- Conceptos clave (badges visuales)
- Fenómenos observados
- Relación con teorías científicas

#### SECCIÓN 7: Conexiones con la Realidad
- ¿Dónde encuentro esto en la vida cotidiana?
- Aplicaciones tecnológicas reales
- Curiosidades científicas

#### SECCIÓN 8: Para Profundizar
- Variaciones del experimento
- Preguntas de reflexión
- Recursos adicionales (videos, artículos)
- Proyectos relacionados

#### SECCIÓN 9: Competencias MEN
Tabla o lista visual:
- Competencias desarrolladas
- DBA relacionados
- Estándares Básicos de Competencias

#### SIDEBAR (Fijo a la Derecha)
- **Asistente de IA** (widget chat)
- Índice navegable de secciones
- "Proyectos Relacionados" (mismo ciclo/área)

---

### 5.4 Asistente de IA (Widget Flotante)

**Ubicación**: Disponible en proyecto.php como widget flotante en esquina inferior derecha.

**Funcionalidades**:
```javascript
// Contexto que se envía a la IA
const contextoProyecto = {
  proyecto_id: <?php echo $proyecto['id']; ?>,
  nombre: "<?php echo $proyecto['nombre']; ?>",
  materiales: <?php echo json_encode($materiales); ?>,
  conceptos: <?php echo json_encode($conceptos_clave); ?>,
  advertencias_seguridad: <?php echo json_encode($seguridad); ?>
};
```

**Interfaz**:
```
┌─────────────────────────────┐
│ 🤖 Asistente de IA          │
│ ¿Tienes dudas sobre este    │
│ proyecto?                    │
├─────────────────────────────┤
│ [Área de chat]              │
│ Usuario: ¿Qué pasa si...?   │
│ IA: [Respuesta contextual]  │
├─────────────────────────────┤
│ [Input de texto]            │
│ Pregúntame algo...    [Enviar]
└─────────────────────────────┘
```

**Guardrails de Seguridad** (validados en API):
- ✅ Responder dudas sobre el proyecto actual
- ✅ Explicar conceptos científicos relacionados
- ✅ Dar tips de montaje/ensamblaje
- ❌ NUNCA sugerir modificaciones peligrosas
- ❌ NUNCA dar instrucciones fuera del alcance del kit
- ❌ Si detecta pregunta de seguridad compleja → "Consulta con tu profesor"

**Implementación Backend**:
```php
// api/ia-consulta.php
<?php
require_once '../config.php';

$proyecto_id = $_POST['proyecto_id'] ?? 0;
$pregunta = $_POST['pregunta'] ?? '';

// Obtener contexto del proyecto
$proyecto = get_proyecto_por_id($pdo, $proyecto_id);
$guia = get_guia_activa($pdo, $proyecto_id);

// Construir prompt para IA
$prompt = construir_prompt($proyecto, $guia, $pregunta);

// Llamar a API de IA (OpenAI, Anthropic, etc.)
$respuesta = llamar_api_ia($prompt);

// Aplicar guardrails
$respuesta_segura = validar_respuesta($respuesta);

echo json_encode(['respuesta' => $respuesta_segura]);
```

---

## 6. BACKEND ADMINISTRATIVO - ESPECIFICACIONES

### 6.1 Dashboard Principal (admin/dashboard.php)

**Widgets de Estadísticas**:
- Proyectos publicados / borradores
- Materiales en catálogo
- Contratos activos / en ejecución
- Visitas del mes (analytics)
- Consultas al asistente IA del mes

**Tablas Resumen**:
- Últimos 5 proyectos editados
- Próximas entregas programadas
- Alertas (ej: materiales sin imagen)

---

### 6.2 Gestión de Proyectos (admin/proyectos/)

#### Lista (index.php)
- Tabla con: Nombre, Ciclo, Áreas, Estado, Acciones
- Filtros rápidos: Por ciclo, Por estado (activo/inactivo)
- Ordenamiento: Por nombre, Por fecha creación, Por popularidad
- Búsqueda
- Botón: "Nuevo Proyecto"

#### Editor (edit.php)
Pestañas del editor:

**TAB 1: Información Básica**
- Nombre del proyecto
- Slug (auto-generado, editable)
- Ciclo (dropdown)
- Grados (checkboxes múltiples)
- Áreas (checkboxes múltiples)
- Dificultad (radio buttons)
- Duración estimada (input numérico + "minutos")
- Resumen (textarea)
- Objetivo de aprendizaje (textarea)

**TAB 2: Multimedia**
- Imagen portada (upload)
- Video portada (URL)
- Galería adicional (múltiples uploads)

**TAB 3: Seguridad**
- Edad mínima (input numérico)
- Edad máxima (input numérico)
- Requiere supervisión (checkbox)
- Advertencias (lista editable):
  - Botón "Agregar Advertencia"
  - Cada ítem: texto + botón eliminar

**TAB 4: SEO**
- SEO Title (input, max 60 caracteres)
- SEO Description (textarea, max 160 caracteres)
- Canonical URL (input, opcional)

**TAB 5: Competencias MEN**
- Competencias (checkboxes de tabla `competencias`)
- DBA relacionados (textarea JSON o inputs múltiples)
- Estándares Básicos (textarea)

**TAB 6: Control**
- Estado: Activo / Inactivo
- Destacado (checkbox)
- Orden de popularidad (input numérico)

Botones finales:
- Guardar y Continuar
- Guardar y Salir
- Cancelar
- Ver Vista Previa

---

### 6.3 Gestión de Guías (admin/guias/edit.php)

Cada proyecto tiene UNA guía activa. Editor de guía:

**SECCIÓN: Introducción**
- Textarea con WYSIWYG (TinyMCE o similar)

**SECCIÓN: Materiales**
Dos subsecciones:

1. **Materiales del Kit**:
   - Selector de materiales de la tabla `materiales`
   - Para cada uno: cantidad + notas
   - Botón "Agregar Material"

2. **Materiales Adicionales**:
   - Lista editable de texto libre
   - Cada ítem: nombre + descripción

**SECCIÓN: Seguridad**
- Textarea WYSIWYG
- Se autocompleta con datos del proyecto, editable

**SECCIÓN: Pasos**
Lista dinámica de pasos:
```
Paso 1:
  - Título: [input]
  - Descripción: [WYSIWYG]
  - Imagen: [upload]
  - Video: [URL input]
  [↑ Mover arriba] [↓ Mover abajo] [🗑 Eliminar]

[+ Agregar Paso]
```

**SECCIÓN: Explicación Científica**
- Textarea WYSIWYG

**SECCIÓN: Conceptos Clave**
- Lista editable (tags):
  - Input + botón "Agregar"
  - Cada concepto aparece como badge removible

**SECCIÓN: Conexiones con la Realidad**
- Textarea WYSIWYG

**SECCIÓN: Para Profundizar**
- Textarea WYSIWYG
- Lista de recursos externos:
  - Título + URL + Tipo (video/artículo/simulación)

Botón: **Publicar Guía** (marca como activa, desactiva versión anterior)

---

### 6.4 Gestión de Contratos CTeI (admin/contratos/)

#### Lista (index.php)
- Tabla: Número, Entidad, Departamento, Valor, Estado, Acciones
- Filtros: Por estado, Por departamento, Por año
- Botón: "Nuevo Contrato"

#### Editor (edit.php)

**TAB 1: Datos Generales**
- Número de contrato
- Entidad contratante
- Departamento (dropdown con departamentos de Colombia)
- Municipios de alcance (selector múltiple)
- Supervisor
- Objeto del contrato (textarea)
- Fecha inicio / Fecha fin (date pickers)
- Valor del contrato (input numérico)

**TAB 2: Alcance del Programa**
- IE beneficiarias (input numérico)
- Estudiantes estimados (input numérico)
- Docentes estimados (input numérico)
- Ciclos incluidos (checkboxes: 1, 2, 3)
- Grados incluidos (checkboxes: 6°, 7°, 8°, 9°, 10°, 11°)

**TAB 3: Proyectos Incluidos**
Tabla dinámica:
```
Proyecto              | Cantidad Kits | Acciones
----------------------|---------------|----------
Radio de cristal      | [100]        | [Eliminar]
Motor eléctrico       | [150]        | [Eliminar]
...
[+ Agregar Proyecto]  → Modal selector
```

**TAB 4: Justificación CTeI**
Formulario para generar documentos:
- Justificación técnica CTeI (textarea WYSIWYG)
- Actividades Decreto 591/1991 aplicables (checkboxes con numerales)
- Alineación Ley 1286/2009 (textarea)
- Competencias MEN globales del programa (auto-calculadas de proyectos, editables)
- Metodología pedagógica (textarea)
- Componente de innovación (textarea sobre IA)

**TAB 5: Indicadores y Metas**
Lista editable:
```
Indicador: [input]
Tipo: [Producto/Resultado]
Meta: [input]
Verificación: [input]
[+ Agregar Indicador]
```

**TAB 6: Exportación de Documentos**
Botones para generar PDFs/Excel:
- 📄 Ficha técnica del programa
- 📄 Portafolio de proyectos seleccionados
- 📊 Matriz de competencias MEN
- 📄 Justificación técnica CTeI
- 📅 Cronograma de ejecución
- 📊 Matriz de indicadores y metas

---

### 6.5 Trazabilidad de Kits (admin/entregas/)

#### Gestión de Lotes (lotes.php)
- Tabla: Código Lote, Proyecto, Cantidad, Estado, Fecha Producción
- Botón: "Nuevo Lote"
- Estados: Producido → En Bodega → Despachado → Entregado

#### Registrar Entrega (registrar.php)
Formulario:
- Seleccionar contrato (dropdown)
- Seleccionar lotes a entregar (checkboxes con cantidades disponibles)
- **Datos de la IE**:
  - Nombre institución educativa
  - Código DANE
  - Municipio
  - Dirección
- **Datos de la Entrega**:
  - Fecha y hora
  - Responsable de entrega (quien entrega por CDC)
  - Responsable de recepción (quien recibe en IE)
  - Cargo del receptor
- **Evidencia**:
  - Observaciones (textarea)
  - Fotos de la entrega (múltiple upload)
  - Firma digital (firma pad o upload de imagen)

Botón: **Generar Acta de Entrega** → Se genera PDF automáticamente

#### Acta PDF (acta-pdf.php)
Genera PDF con:
```
ACTA DE ENTREGA No. CDC-2025-XXXX

CONTRATO: [Número] con [Entidad]

INSTITUCIÓN EDUCATIVA:
- Nombre: [...]
- DANE: [...]
- Municipio: [...]

DETALLE DE KITS ENTREGADOS:
┌────────────────────────────┬──────────┐
│ Proyecto                   │ Cantidad │
├────────────────────────────┼──────────┤
│ Radio de cristal           │    15    │
│ Motor eléctrico simple     │    15    │
└────────────────────────────┴──────────┘

ENTREGA REALIZADA POR:
Nombre: [...]
Fecha: [...]

RECIBIDO POR:
Nombre: [...]
Cargo: [...]
Firma: [imagen de firma]

OBSERVACIONES:
[...]

EVIDENCIA FOTOGRÁFICA:
[imágenes]
```

---

### 6.6 Analytics (admin/analytics/)

#### Dashboard General (dashboard.php)
Métricas clave:
- Visitas totales (mes actual vs mes anterior)
- Usuarios únicos estimados (por sesion_hash)
- Proyectos más vistos (top 10)
- Tiempo promedio en sitio
- Dispositivos (% móvil / tablet / desktop)
- Consultas al asistente IA

Gráficos:
- Visitas diarias (últimos 30 días)
- Distribución por ciclo (% visitas)
- Distribución por área (% visitas)

#### Por Proyecto (por-proyecto.php)
- Selector de proyecto
- Visitas totales
- Tiempo promedio en página
- Tasa de descarga de PDF
- Consultas IA específicas de este proyecto
- Materiales más consultados

#### Geográfico (geografico.php)
- Mapa de calor de Colombia por departamento
- Tabla: Departamento | Ciudad | Visitas
- Filtro por contrato (para ver si el acceso viene del departamento contratado)

---

## 7. CONSIDERACIONES TÉCNICAS

### 7.1 Stack Tecnológico

**Backend**:
- PHP 8.1+
- MySQL 8.0+
- PDO para conexión DB
- Session-based authentication

**Frontend**:
- HTML5 + CSS3
- JavaScript Vanilla (sin frameworks pesados)
- Fetch API para AJAX
- Progressive Enhancement

**Librerías**:
- TinyMCE o CKEditor (WYSIWYG para admin)
- Chart.js (gráficos en analytics)
- TCPDF o mPDF (generación de PDFs)
- PHPSpreadsheet (exportación Excel)

**Infraestructura**:
- Apache/Nginx con .htaccess
- HTTPS obligatorio
- CDN para assets estáticos (opcional)

---

### 7.2 Performance y Optimización

**Objetivos**:
- Página principal: < 2s carga completa
- Guía de proyecto: < 3s carga completa
- Funcional con conexiones lentas (3G)

**Estrategias**:
- Imágenes optimizadas y comprimidas (WebP + fallback JPG)
- Lazy loading de imágenes
- Minificación de CSS/JS
- Caché de consultas frecuentes (headers HTTP)
- Índices en tablas de DB
- Paginación en listados largos

---

### 7.3 Seguridad

**Medidas**:
- Prepared statements (PDO) - prevención SQL injection
- Escapado de salida (htmlspecialchars) - prevención XSS
- CSRF tokens en formularios admin
- Validación de uploads (tipo, tamaño, extensión)
- Rate limiting en API de IA
- Session timeout en admin
- Logs de acciones críticas

---

### 7.4 Accesibilidad

**WCAG 2.1 AA**:
- Navegación por teclado completa
- ARIA labels en controles interactivos
- Contraste de colores adecuado
- Alt text en todas las imágenes
- Encabezados semánticos (h1, h2, h3...)
- Skip links

---

### 7.5 SEO

**On-page**:
- Title y meta description únicos por página
- Canonical URLs
- Schema.org markup (HowTo, EducationalOrganization)
- URLs amigables (slug-based)
- Sitemap.xml dinámico
- robots.txt configurado

**Sitemap Dinámico** (sitemap.xml.php):
```php
<?php
header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Homepage
echo '<url><loc>' . SITE_URL . '/</loc><priority>1.0</priority></url>';

// Proyectos
$proyectos = get_proyectos_activos($pdo);
foreach ($proyectos as $p) {
    echo '<url>';
    echo '<loc>' . SITE_URL . '/proyecto.php?slug=' . $p['slug'] . '</loc>';
    echo '<lastmod>' . date('Y-m-d', strtotime($p['updated_at'])) . '</lastmod>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

// Otras páginas...
echo '</urlset>';
```

---

## 8. DIFERENCIAS CON LOS PROYECTOS EXISTENTES

### Adaptaciones de The Green Almanac:
| The Green Almanac | Clase de Ciencia |
|-------------------|------------------|
| `articles` | `proyectos` |
| `sections` (temáticas) | `ciclos` (grados escolares) |
| `tags` | `areas` + `competencias` |
| `materials` → links externos | `materiales` → parte de kits |
| `issues` (revista) | `contratos` (CTeI) |
| Tracking de clicks a ecommerce | Analytics de aprendizaje |
| Formato: howto/recipe/reference | Solo instructivos (guías) |

### Adaptaciones de Free Science Project:
| Free Science Project | Clase de Ciencia |
|---------------------|------------------|
| 4 categorías (K-12) | 3 ciclos (6°-11°) |
| Proyectos en carpetas estáticas | Proyectos en base de datos |
| Sin backend | Backend completo |
| Sin asistente IA | Asistente IA integrado |
| Información básica | Alineación MEN detallada |

---

## 9. CRONOGRAMA SUGERIDO DE DESARROLLO

### FASE 1: Fundamentos (Semanas 1-2)
- [ ] Configuración de entorno (servidor, DB)
- [ ] Creación de base de datos completa
- [ ] Estructura de carpetas
- [ ] Configuración inicial (config.php, .htaccess)
- [ ] Sistema de autenticación admin

### FASE 2: Backend Admin - Proyectos (Semanas 3-4)
- [ ] Dashboard principal
- [ ] CRUD de proyectos (info básica)
- [ ] CRUD de guías (estructura completa)
- [ ] Gestión de materiales
- [ ] Sistema de uploads

### FASE 3: Frontend Público - Core (Semanas 5-6)
- [ ] Homepage
- [ ] Catálogo con filtros
- [ ] Página de guía (proyecto.php) - estructura completa
- [ ] Sistema de navegación
- [ ] Búsqueda

### FASE 4: Asistente IA (Semana 7)
- [ ] Integración con API de IA
- [ ] Widget flotante frontend
- [ ] Sistema de contexto
- [ ] Guardrails de seguridad
- [ ] Logging de consultas

### FASE 5: Backend Admin - CTeI (Semanas 8-9)
- [ ] Gestión de contratos
- [ ] Alcance y justificación
- [ ] Trazabilidad de kits (lotes, entregas)
- [ ] Generación de actas PDF
- [ ] Exportación de documentos

### FASE 6: Analytics (Semana 10)
- [ ] Sistema de tracking frontend
- [ ] Dashboard de analytics
- [ ] Reportes por proyecto
- [ ] Vista geográfica

### FASE 7: Contenido Inicial (Semanas 11-12)
- [ ] Carga de 15 proyectos propuestos
- [ ] Redacción de guías completas
- [ ] Fotografía/diagramas
- [ ] Revisión de alineación MEN

### FASE 8: Testing y Optimización (Semanas 13-14)
- [ ] Testing de funcionalidades
- [ ] Optimización de performance
- [ ] Testing en dispositivos móviles
- [ ] Ajustes de SEO
- [ ] Testing de seguridad

### FASE 9: Deploy y Lanzamiento (Semana 15)
- [ ] Configuración de servidor producción
- [ ] Migración de contenido
- [ ] Configuración de SSL
- [ ] Testing en producción
- [ ] Lanzamiento

---

## 10. PRÓXIMOS PASOS (Antes de Codificar)

### 10.1 Decisiones Pendientes

**CRÍTICO - Integración IA**:
- ¿Qué API de IA usar? (OpenAI GPT-4, Anthropic Claude, local con LLaMA?)
- ¿Cómo manejar costos de API?
- ¿Implementar rate limiting por proyecto o por sesión?

**IMPORTANTE - Materiales**:
- ¿Quién provee los kits? ¿Hay proveedor definido?
- ¿Los materiales son genéricos o hay productos específicos con links?
- ¿Necesitamos tracking de stock de materiales?

**IMPORTANTE - Hosting**:
- ¿Dónde se alojará? (VPS propio, hosting compartido, cloud?)
- ¿Proyección de tráfico? (usuarios concurrentes esperados)

**OPCIONAL - Funcionalidades Futuras**:
- ¿Sistema de comentarios/Q&A?
- ¿Gamificación? (badges, progreso)
- ¿Integración con LMS escolares? (Moodle, Google Classroom)

### 10.2 Validaciones con Stakeholders

**Equipo Pedagógico**:
- Revisar estructura de guías
- Validar alineación con competencias MEN
- Confirmar información de seguridad requerida

**Equipo Comercial/CTeI**:
- Revisar sección de contratos
- Validar campos de justificación CTeI
- Confirmar requerimientos de trazabilidad

**Equipo Técnico**:
- Confirmar stack tecnológico
- Revisar requisitos de infraestructura
- Evaluar alternativas de IA

---

## 11. ANEXOS

### ANEXO A: Queries SQL Clave

**(Ver secciones anteriores del documento para tablas completas)**

### ANEXO B: Mockups de Interfaz

**(A desarrollar con herramienta de diseño - Figma, Sketch, etc.)**

### ANEXO C: Competencias MEN de Referencia

**Competencias de Indagación**:
1. Observo fenómenos específicos
2. Formulo preguntas
3. Formulo hipótesis
4. Realizo mediciones
5. Registro observaciones
6. Analizo resultados
7. Comunico resultados

**Competencias de Explicación**:
1. Establezco relaciones causales
2. Modelo fenómenos
3. Uso conceptos científicos
4. Argumento con evidencia

**Competencias de Uso del Conocimiento**:
1. Aplico conocimientos a situaciones
2. Propongo soluciones
3. Tomo decisiones informadas

### ANEXO D: Ejemplos de Proyectos (con datos completos)

#### PROYECTO EJEMPLO 1: Radio de Cristal

```json
{
  "nombre": "Radio de Cristal",
  "slug": "radio-de-cristal",
  "ciclo": "2",
  "grados": [8, 9],
  "areas": ["Física", "Tecnología"],
  "duracion_minutos": 90,
  "dificultad": "medio",
  "resumen": "Construye un receptor de radio AM que funciona sin baterías, aprovechando únicamente la energía de las ondas electromagnéticas. Aprende sobre radiofrecuencia, diodos detectores y diseño de antenas.",
  "objetivo_aprendizaje": "Comprender el funcionamiento de las ondas electromagnéticas y su aplicación en las comunicaciones, mediante la construcción y operación de un receptor de radio de cristal.",
  "seguridad": {
    "edad_min": 11,
    "edad_max": 17,
    "requiere_supervision": true,
    "advertencias": [
      "Manipular con cuidado el diodo detector - es delicado",
      "No conectar a corriente eléctrica - solo ondas de radio",
      "Verificar que la antena no toque cables eléctricos"
    ]
  },
  "competencias_men": [
    "Explica el comportamiento de las ondas electromagnéticas",
    "Relaciona conceptos de física con aplicaciones tecnológicas",
    "Diseña y construye dispositivos electrónicos simples"
  ],
  "conceptos_clave": [
    "Ondas electromagnéticas",
    "Radiofrecuencia AM",
    "Diodo detector",
    "Resonancia",
    "Antena"
  ]
}
```

**Materiales del Kit**:
- 1 Diodo detector (germanio)
- 1 Capacitor variable 100-365pF
- 50 metros cable esmaltado calibre 26
- 1 Audífono piezoelétrico de alta impedancia
- 1 Tubete de ferrita
- Clips de conexión

**Materiales Adicionales**:
- Madera para base (20x15 cm)
- Cable para antena externa (10-20 metros)
- Conexión a tierra (tubería de agua)

**Pasos** (resumen):
1. Montar base de madera
2. Enrollar bobina en tubete de ferrita (80 vueltas)
3. Conectar capacitor variable
4. Conectar diodo detector
5. Conectar audífono
6. Instalar antena externa
7. Conectar a tierra
8. Sintonizar girando el capacitor

---

## 12. CONCLUSIONES Y RECOMENDACIONES

### Lo que funciona bien de The Green Almanac y debemos mantener:
✅ Arquitectura de base de datos limpia y escalable
✅ Sistema de filtros múltiples con SQL dinámico
✅ Backend admin simple pero completo
✅ Optimización para móviles y bajo ancho de banda
✅ SEO bien implementado

### Lo que funciona bien de Free Science Project y debemos adaptar:
✅ Organización clara por niveles educativos
✅ Estructura de proyectos individuales con pasos detallados
✅ Filtros visuales intuitivos
✅ Integración con kits comerciales

### Innovaciones de Clase de Ciencia:
🚀 Asistente de IA contextual
🚀 Sistema de gestión contractual CTeI
🚀 Trazabilidad completa de kits
🚀 Alineación detallada con competencias MEN
🚀 Analytics de aprendizaje

### Riesgos Identificados:
⚠️ **Complejidad del asistente IA**: Requiere testing exhaustivo de guardrails
⚠️ **Costos de API**: Necesita modelo de uso sostenible
⚠️ **Volumen de contenido**: 15 proyectos con guías completas = mucho contenido inicial
⚠️ **Dependencia de kits físicos**: El modelo solo funciona si hay distribución efectiva

### Recomendación Final:
**Proceder con desarrollo incremental**: Comenzar con MVP que incluya:
1. Frontend público básico (homepage + catálogo + 3 proyectos completos)
2. Backend admin funcional (CRUD proyectos + guías)
3. Asistente IA en modo beta (1 proyecto piloto)
4. Gestión de contratos simplificada (fase 1)

Iterar y expandir basándose en feedback real de:
- Estudiantes usando las guías
- Docentes implementando en clase
- Administradores gestionando contratos
- Usuarios del asistente IA

---

**Documento generado el**: 19 de Diciembre 2025  
**Versión**: 1.0  
**Para**: Desarrollo de clasedeciencia.com  
**Próximo paso**: Revisión y aprobación → Crear especificaciones técnicas detalladas por módulo
