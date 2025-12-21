<?php
// Clase - Detalle de la clase con guía
require_once 'config.php';
require_once 'includes/functions.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (!$slug) {
    header('Location: /clases');
    exit;
}

// Cargar clase
$stmt = $pdo->prepare("SELECT * FROM clases WHERE slug = ? AND activo = 1");
$stmt->execute([$slug]);
$proyecto = $stmt->fetch();
if (!$proyecto) {
    header('Location: /clases');
    exit;
}

// Cargar información completa del ciclo
$ciclo_info = [];
if (!empty($proyecto['ciclo'])) {
    $stmt = $pdo->prepare("SELECT * FROM ciclos WHERE numero = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$proyecto['ciclo']]);
    $ciclo_info = $stmt->fetch();
}

// Cargar áreas asociadas
$stmt = $pdo->prepare("SELECT a.* FROM areas a JOIN clase_areas ca ON a.id = ca.area_id WHERE ca.clase_id = ? ORDER BY a.nombre");
$stmt->execute([$proyecto['id']]);
$areas = $stmt->fetchAll();

// Cargar competencias MEN asociadas
$stmt = $pdo->prepare("SELECT c.id, c.codigo, c.subcategoria, c.nombre, c.explicacion FROM competencias c JOIN clase_competencias cc ON c.id = cc.competencia_id WHERE cc.clase_id = ? ORDER BY c.subcategoria, c.id");
$stmt->execute([$proyecto['id']]);
$competencias = $stmt->fetchAll();

// Cargar tags
$stmt = $pdo->prepare("SELECT tag FROM clase_tags WHERE clase_id = ? ORDER BY tag");
$stmt->execute([$proyecto['id']]);
$tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Cargar guía (última versión)
$stmt = $pdo->prepare("SELECT * FROM guias WHERE clase_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$proyecto['id']]);
$guia = $stmt->fetch();

// Kits asociados (nueva relación N:M)
$stmt = $pdo->prepare("
    SELECT k.*, ck.es_principal, ck.sort_order 
    FROM kits k 
    JOIN clase_kits ck ON k.id = ck.kit_id 
    WHERE ck.clase_id = ? 
    ORDER BY ck.es_principal DESC, ck.sort_order ASC
");
$stmt->execute([$proyecto['id']]);
$kits = $stmt->fetchAll();

// Componentes de todos los kits
$materiales_por_kit = [];
foreach ($kits as $kit) {
    $stmt = $pdo->prepare("
        SELECT kc.*, i.nombre_comun, i.slug, i.sku, i.unidad, i.advertencias_seguridad 
        FROM kit_componentes kc 
        JOIN kit_items i ON kc.item_id = i.id 
        WHERE kc.kit_id = ? 
        ORDER BY kc.sort_order ASC, i.nombre_comun ASC
    ");
    $stmt->execute([(int)$kit['id']]);
    $materiales_por_kit[$kit['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Multimedia
$stmt = $pdo->prepare("SELECT * FROM recursos_multimedia WHERE clase_id = ? ORDER BY sort_order");
$stmt->execute([$proyecto['id']]);
$recursos = $stmt->fetchAll();

// Ficha técnica (atributos de clase)
$ficha_rows = [];
try {
    $stmt = $pdo->prepare("SELECT c.atributo_id, c.valor_string, c.valor_numero, c.valor_entero, c.valor_booleano, c.valor_fecha, c.valor_datetime, c.valor_json, c.unidad_codigo, c.orden,
                                   d.etiqueta, d.tipo_dato, d.unidad_defecto,
                                   COALESCE(m.orden, 9999) AS map_orden
                              FROM atributos_contenidos c
                              JOIN atributos_definiciones d ON d.id = c.atributo_id
                              LEFT JOIN atributos_mapeo m ON m.atributo_id = c.atributo_id AND m.tipo_entidad = 'clase'
                             WHERE c.tipo_entidad = 'clase' AND c.entidad_id = ?
                             ORDER BY map_orden ASC, c.atributo_id ASC, c.orden ASC, c.id ASC");
    $stmt->execute([$proyecto['id']]);
    $ficha_rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log('Error ficha tecnica: ' . $e->getMessage());
    $ficha_rows = [];
}

$ficha_attrs = [];
foreach ($ficha_rows as $r) {
    $aid = (int)$r['atributo_id'];
    if (!isset($ficha_attrs[$aid])) {
        $ficha_attrs[$aid] = [
            'label' => $r['etiqueta'],
            'tipo' => $r['tipo_dato'],
            'unidad_def' => $r['unidad_defecto'] ?? '',
            'values' => []
        ];
    }
    $tipo = $r['tipo_dato'];
    $unit = $r['unidad_codigo'] ?: '';
    $val = '';
    if ($tipo === 'number') { $val = $r['valor_numero'] !== null ? rtrim(rtrim((string)$r['valor_numero'], '0'), '.') : ''; }
    elseif ($tipo === 'integer') { $val = $r['valor_entero'] !== null ? (string)$r['valor_entero'] : ''; }
    elseif ($tipo === 'boolean') { $val = ((int)$r['valor_booleano'] === 1 ? 'Sí' : 'No'); }
    elseif ($tipo === 'date') { $val = $r['valor_fecha'] ?: ''; }
    elseif ($tipo === 'datetime') { $val = $r['valor_datetime'] ?: ''; }
    elseif ($tipo === 'json') { $val = $r['valor_json'] ?: ''; }
    else { $val = $r['valor_string'] ?: ''; }
    if ($val === '' || $val === null) continue;
    $ficha_attrs[$aid]['values'][] = [ 'text' => (string)$val, 'unit' => $unit ];
}

// Clases relacionadas (por área o competencia)
$clases_relacionadas = [];
if (!empty($areas)) {
    $area_ids = array_column($areas, 'id');
    $placeholders = implode(',', array_fill(0, count($area_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.* 
        FROM clases c
        JOIN clase_areas ca ON c.id = ca.clase_id
        WHERE ca.area_id IN ($placeholders) 
        AND c.id != ? 
        AND c.activo = 1
        ORDER BY c.destacado DESC, RAND()
        LIMIT 3
    ");
    $stmt->execute([...$area_ids, $proyecto['id']]);
    $clases_relacionadas = $stmt->fetchAll();
}

$page_title = $proyecto['seo_title'] ?: ($proyecto['nombre'] . ' - Clase de Ciencia');
$page_description = $proyecto['seo_description'] ?: ($proyecto['resumen'] ?: 'Guía interactiva de la clase');
$canonical_url = SITE_URL . '/' . $proyecto['slug'];

// Schema.org básico HowTo
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'HowTo',
    'name' => $proyecto['nombre'],
    'description' => $page_description,
    'totalTime' => 'PT' . (int)$proyecto['duracion_minutos'] . 'M',
    'url' => $canonical_url
];
if ($guia && !empty($guia['pasos'])) {
    $pasos = json_decode($guia['pasos'], true) ?: [];
    $schema['step'] = array_map(function($i, $p){
        return [
            '@type' => 'HowToStep',
            'name' => isset($p['titulo']) ? $p['titulo'] : ('Paso ' . ($i+1)),
            'text' => isset($p['texto']) ? $p['texto'] : ''
        ];
    }, array_keys($pasos), $pasos);
}
$schema_json = json_encode($schema, JSON_UNESCAPED_UNICODE);
// Prevent </script> early-termination in JSON-LD
$schema_json = str_replace('</script>', '<\/script>', $schema_json);

include 'includes/header.php';
?>
<div class="container article-page">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <a href="/clases">Clases</a> / <strong><?= h($proyecto['nombre']) ?></strong>
    </div>
    
    <!-- Card de Resumen Técnico -->
    <div class="clase-summary-card">
        <div class="summary-content">
            <div class="summary-left">
                <?php if (!empty($proyecto['imagen_portada'])): ?>
                    <img src="<?= h($proyecto['imagen_portada']) ?>" alt="<?= h($proyecto['nombre']) ?>" class="summary-image" onerror="this.onerror=null; console.log('❌ [Clase] Imagen portada falló'); var p=document.createElement('div'); p.className='summary-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='🔬'; p.appendChild(s); this.replaceWith(p);" />
                <?php else: ?>
                    <div class="summary-placeholder">
                        <span class="placeholder-icon">🔬</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="summary-right">
                <div class="summary-header">
                    <h1 class="summary-title"><?= h($proyecto['nombre']) ?></h1>
                    <?php if (!empty($proyecto['destacado'])): ?>
                        <span class="badge badge-destacado" title="Recomendado">⭐ Destacado</span>
                    <?php endif; ?>
                </div>
                
                <div class="summary-specs">
                    <div class="spec-item spec-ciclo-clickable" onclick="toggleCicloModal()" title="Click para ver más información">
                        <span class="spec-label">📚 Ciclo</span>
                        <span class="spec-value">
                            Ciclo <?= h($proyecto['ciclo']) ?>: <?= !empty($ciclo_info) ? h($ciclo_info['nombre']) : '' ?>
                            <span class="info-icon">ℹ️</span>
                        </span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">🎓 Grados</span>
                        <span class="spec-value">
                            <?php 
                            if (!empty($proyecto['grados'])) {
                                $grados = json_decode($proyecto['grados'], true);
                                if (is_array($grados) && count($grados) > 0) {
                                    echo implode(', ', array_map(fn($g) => $g . '°', $grados));
                                }
                            }
                            ?>
                        </span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">📊 Dificultad</span>
                        <span class="spec-value difficulty-<?= h($proyecto['dificultad']) ?>"><?= h(ucfirst($proyecto['dificultad'])) ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">⏱️ Duración</span>
                        <span class="spec-value"><?= (int)$proyecto['duracion_minutos'] ?> minutos</span>
                    </div>
                    <?php if (!empty($areas)): ?>
                    <div class="spec-item spec-item-full">
                        <span class="spec-label">🔬 Áreas</span>
                        <span class="spec-value">
                            <?php foreach ($areas as $idx => $area): ?>
                                <a href="/clases?area=<?= h($area['slug']) ?>" class="area-link"><?= h($area['nombre']) ?></a><?= $idx < count($areas) - 1 ? ', ' : '' ?>
                            <?php endforeach; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($proyecto['objetivo_aprendizaje'])): ?>
                <div class="summary-objetivo">
                    <h3 class="objetivo-label">🎯 Objetivo de Aprendizaje</h3>
                    <p class="objetivo-content"><?= h($proyecto['objetivo_aprendizaje']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <article>
        <?php if (!empty($proyecto['resumen'])): ?>
        <div class="resumen-section">
            <p class="lead"><?= h($proyecto['resumen']) ?></p>
        </div>
        <?php endif; ?>

        <?php /* Ficha técnica se mostrará al final en la byline */ ?>

        <?php 
        // Información de seguridad estructurada
        if (!empty($proyecto['seguridad'])) {
            $seguridad = json_decode($proyecto['seguridad'], true);
            if (is_array($seguridad) && (!empty($seguridad['edad_min']) || !empty($seguridad['notas']))): 
        ?>
        <section class="safety-info">
            <h2 class="safety-title">⚠️ Información de Seguridad</h2>
            <div class="safety-content">
                <?php if (!empty($seguridad['edad_min']) && !empty($seguridad['edad_max'])): ?>
                    <p class="edad-recomendada"><strong>👥 Edad recomendada:</strong> <?= (int)$seguridad['edad_min'] ?> a <?= (int)$seguridad['edad_max'] ?> años</p>
                <?php endif; ?>
                <?php if (!empty($seguridad['notas'])): ?>
                    <div class="safety-notes"><?= nl2br(h($seguridad['notas'])) ?></div>
                <?php endif; ?>
            </div>
        </section>
        <?php 
            endif;
        }
        ?>
        
        <?php if (!empty($proyecto['video_portada'])): ?>
        <div class="video-portada-section">
            <h2>🎥 Video Introductorio</h2>
            <div class="video-wrapper">
                <iframe src="<?= h($proyecto['video_portada']) ?>" title="Video de <?= h($proyecto['nombre']) ?>" allowfullscreen></iframe>
            </div>
        </div>
        <?php endif; ?>

        <section class="article-content">
            <?php if (!empty($proyecto['contenido_html'])): ?>
                <!-- Contenido principal con formato rico -->
                <div class="article-body">
                    <?= $proyecto['contenido_html'] ?>
                </div>
            <?php elseif ($guia): ?>
                <!-- Guía básica como fallback -->
                <div class="article-body">
                    <?php if (!empty($guia['introduccion'])): ?>
                        <h2>Introducción</h2>
                        <p><?= h($guia['introduccion']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($guia['seccion_seguridad'])): ?>
                        <h2>Seguridad</h2>
                        <p><?= h($guia['seccion_seguridad']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($guia['pasos'])): ?>
                        <h2>Pasos</h2>
                        <?php $pasos = json_decode($guia['pasos'], true) ?: []; ?>
                        <ol>
                            <?php foreach ($pasos as $idx => $p): ?>
                                <li>
                                    <strong><?= h($p['titulo'] ?? ('Paso ' . ($idx+1))) ?></strong>
                                    <p><?= h($p['texto'] ?? '') ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <?php if (!empty($guia['explicacion_cientifica'])): ?>
                        <h2>Explicación Científica</h2>
                        <p><?= h($guia['explicacion_cientifica']) ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="no-content">El contenido detallado de esta clase se encuentra en desarrollo. Por favor contacta a tu docente para más información.</p>
            <?php endif; ?>
        </section>

        <?php if (!empty($kits)): ?>
        <section class="kits-section">
            <h2>📦 Kits de Materiales</h2>
            <?php foreach ($kits as $kit): ?>
                <div class="kit-card">
                    <div class="kit-header">
                        <h3>
                            <a href="/<?= h($kit['slug'] ?? '') ?>" style="text-decoration:none;">
                                <?= h($kit['nombre']) ?>
                            </a>
                            <a href="/<?= h($kit['slug'] ?? '') ?>" class="icon-link" title="Ver kit" aria-label="Ver kit <?= h($kit['nombre']) ?>" style="margin-left:8px;">
                                🔎
                            </a>
                            <?php if (!empty($kit['es_principal'])): ?>
                                <span class="badge badge-primary">Kit Principal</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Kit Opcional</span>
                            <?php endif; ?>
                        </h3>
                        <!-- Código eliminado por no ser necesario en la vista pública -->
                    </div>
                    <?php if (!empty($materiales_por_kit[$kit['id']])): ?>
                        <h4>Componentes incluidos:</h4>
                        <ul class="materials-list">
                            <?php foreach ($materiales_por_kit[$kit['id']] as $m): ?>
                                <li>
                                    <span class="material-name"><?= h($m['nombre_comun']) ?></span>
                                    <?php if (!empty($m['slug'])): ?>
                                        <a href="/<?= h($m['slug']) ?>" class="icon-link" title="Ver componente" aria-label="Ver componente <?= h($m['nombre_comun']) ?>" style="margin-left:6px; text-decoration:none;">🔎</a>
                                    <?php endif; ?>
                                    <?php if (!empty($m['advertencias_seguridad'])): ?>
                                        <small class="material-warning">⚠️ <?= h($m['advertencias_seguridad']) ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($m['cantidad'])): ?>
                                        <span class="badge"><?= h($m['cantidad']) ?> <?= h($m['unidad'] ?? '') ?></span>
                                    <?php endif; ?>
                                    <?php if (isset($m['es_incluido_kit']) && (int)$m['es_incluido_kit'] === 1): ?>
                                        <span class="badge badge-success">✓ Incluido</span>
                                    <?php endif; ?>
                                    <?php if (!empty($m['notas'])): ?>
                                        <small class="material-notes"><?= h($m['notas']) ?></small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>

        <?php if (!empty($competencias)): ?>
        <section class="competencias-section">
            <h2>📚 Competencias Desarrolladas</h2>
            <p class="competencias-intro">Esta clase desarrolla las siguientes competencias educativas:</p>
            <div class="competencias-accordion">
                <?php foreach ($competencias as $idx => $comp): ?>
                    <div class="competencia-item">
                        <button class="competencia-header" onclick="toggleCompetencia(<?= $idx ?>)" type="button">
                            <span class="competencia-title">
                                <strong class="competencia-codigo"><?= h($comp['codigo']) ?></strong>
                                <span class="competencia-nombre"><?= h($comp['nombre']) ?></span>
                            </span>
                            <span class="toggle-icon" id="icon-<?= $idx ?>">▼</span>
                        </button>
                        <div class="competencia-content" id="content-<?= $idx ?>" style="display: none;">
                            <?php if (!empty($comp['subcategoria'])): ?>
                                <p class="competencia-subcategoria"><strong>Categoría:</strong> <?= h($comp['subcategoria']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($comp['explicacion'])): ?>
                                <p class="competencia-explicacion"><?= h($comp['explicacion']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($recursos)): ?>
        <section class="multimedia">
            <h2>🎞️ Recursos Multimedia Adicionales</h2>
            <div class="gallery">
                <?php foreach ($recursos as $r): ?>
                    <?php if ($r['tipo'] === 'imagen'): ?>
                        <div class="media-item">
                            <img src="<?= h($r['url']) ?>" alt="<?= h($r['titulo'] ?? 'Imagen') ?>" onerror="this.onerror=null; console.log('❌ [Clase] Recurso imagen falló'); var p=document.createElement('div'); p.className='gallery-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='🔬'; p.appendChild(s); this.replaceWith(p);" />
                            <?php if (!empty($r['titulo'])): ?>
                                <p class="media-caption"><?= h($r['titulo']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($r['tipo'] === 'video'): ?>
                        <div class="media-item">
                            <div class="video-wrapper">
                                <iframe src="<?= h($r['url']) ?>" title="<?= h($r['titulo'] ?? 'Video') ?>" allowfullscreen></iframe>
                            </div>
                            <?php if (!empty($r['titulo'])): ?>
                                <p class="media-caption"><?= h($r['titulo']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($r['tipo'] === 'pdf'): ?>
                        <div class="media-item">
                            <a class="btn btn-secondary" href="<?= h($r['url']) ?>" target="_blank">
                                📄 <?= h($r['titulo'] ?? 'Descargar PDF') ?>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php
        // Construir ficha técnica en línea (resumen compacto)
        $ficha_inline = '';
        if (!empty($ficha_attrs)) {
            $parts = [];
            $count = 0; $max = 5; // limitar para evitar líneas demasiado largas
            foreach ($ficha_attrs as $attr) {
                if ($count >= $max) { break; }
                $vals = $attr['values'];
                $units = array_values(array_unique(array_filter(array_map(fn($v)=>$v['unit'] ?? '', $vals))));
                $singleUnit = count($units) === 1 ? $units[0] : '';
                $texts = array_map(function($v) use ($singleUnit){
                    $t = (string)$v['text'];
                    if ($singleUnit === '' && !empty($v['unit'])) $t .= ' ' . $v['unit'];
                    return $t;
                }, $vals);
                $display = implode(', ', $texts);
                if ($singleUnit) { $display .= ' ' . $singleUnit; }
                $parts[] = ($attr['label'] . ': ' . $display);
                $count++;
            }
            if (!empty($parts)) {
                $ficha_inline = implode(' · ', $parts);
                if (count($ficha_attrs) > $max) { $ficha_inline .= '…'; }
            }
        }
        ?>
        <?php if (!empty($proyecto['autor']) || !empty($proyecto['published_at']) || $ficha_inline !== ''): ?>
        <div class="article-byline">
            <?php if (!empty($proyecto['autor'])): ?>
                <span class="author">✍️ <?= h($proyecto['autor']) ?></span>
            <?php endif; ?>
            <?php if (!empty($proyecto['published_at'])): ?>
                <span class="date">📅 Publicado: <?= date('d/m/Y', strtotime($proyecto['published_at'])) ?></span>
            <?php endif; ?>
            <?php if (!empty($proyecto['updated_at']) && $proyecto['updated_at'] !== $proyecto['published_at']): ?>
                <span class="updated">🔄 Actualizado: <?= date('d/m/Y', strtotime($proyecto['updated_at'])) ?></span>
            <?php endif; ?>
            <?php if ($ficha_inline !== ''): ?>
                <span class="ficha">🧪 <?= h($ficha_inline) ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($tags)): ?>
        <section class="tags-section">
            <h3>🏷️ Tags</h3>
            <div class="tags-container">
                <?php foreach ($tags as $tag): ?>
                    <a href="/clases?busqueda=<?= urlencode($tag) ?>" class="tag-pill"><?= h($tag) ?></a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($clases_relacionadas)): ?>
        <section class="related-classes">
            <h2>🔗 Clases Relacionadas</h2>
            <div class="related-grid">
                <?php foreach ($clases_relacionadas as $rel): ?>
                    <a href="/<?= h($rel['slug']) ?>" class="related-card">
                        <?php if (!empty($rel['imagen_portada'])): ?>
                            <img src="<?= h($rel['imagen_portada']) ?>" alt="<?= h($rel['nombre']) ?>" class="related-thumbnail" onerror="this.onerror=null; console.log('❌ [Clase] Miniatura relacionada falló'); var p=document.createElement('div'); p.className='thumbnail-placeholder error'; var s=document.createElement('span'); s.className='placeholder-icon'; s.textContent='🔬'; p.appendChild(s); this.replaceWith(p);" />
                        <?php endif; ?>
                        <div class="related-info">
                            <h4><?= h($rel['nombre']) ?></h4>
                            <div class="related-meta">
                                <span class="badge">Ciclo <?= h($rel['ciclo']) ?></span>
                                <span class="badge"><?= h(ucfirst($rel['dificultad'])) ?></span>
                            </div>
                            <?php if (!empty($rel['resumen'])): ?>
                                <p class="related-excerpt"><?= h(mb_substr($rel['resumen'], 0, 100)) ?>...</p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </article>
    
    <!-- Modal de Información del Ciclo -->
    <?php if (!empty($ciclo_info)): ?>
    <div id="cicloModal" class="modal-overlay" onclick="toggleCicloModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="toggleCicloModal()">&times;</button>
            <div class="modal-header">
                <h2>📚 Ciclo <?= h($ciclo_info['numero']) ?>: <?= h($ciclo_info['nombre']) ?></h2>
            </div>
            <div class="modal-body">
                <div class="ciclo-info-grid">
                    <div class="ciclo-info-item">
                        <strong>🎓 Grados:</strong>
                        <span><?= h($ciclo_info['grados_texto']) ?></span>
                    </div>
                    <div class="ciclo-info-item">
                        <strong>👥 Edad:</strong>
                        <span><?= h($ciclo_info['edad_min']) ?> a <?= h($ciclo_info['edad_max']) ?> años</span>
                    </div>
                    <div class="ciclo-info-item">
                        <strong>📖 Nivel Educativo:</strong>
                        <span><?= h($ciclo_info['nivel_educativo']) ?></span>
                    </div>
                    <div class="ciclo-info-item">
                        <strong>🌍 ISCED:</strong>
                        <span><?= h($ciclo_info['isced_level']) ?></span>
                    </div>
                </div>
                
                <div class="ciclo-proposito">
                    <h3>🎯 Propósito</h3>
                    <p><?= h($ciclo_info['proposito']) ?></p>
                </div>
                
                <?php if (!empty($ciclo_info['explicacion'])): ?>
                <div class="ciclo-explicacion">
                    <h3>📝 Explicación Detallada</h3>
                    <p><?= h($ciclo_info['explicacion']) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="modal-actions">
                    <a href="/clases?ciclo=<?= h($ciclo_info['numero']) ?>" class="btn btn-primary">Ver Clases de este Ciclo</a>
                    <button onclick="toggleCicloModal()" class="btn btn-secondary">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<script>
function toggleCicloModal() {
    const modal = document.getElementById('cicloModal');
    if (modal) {
        modal.classList.toggle('active');
    }
}

function toggleCompetencia(index) {
    const content = document.getElementById('content-' + index);
    const icon = document.getElementById('icon-' + index);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.textContent = '▲';
    } else {
        content.style.display = 'none';
        icon.textContent = '▼';
    }
}

console.log('🔍 [Clase] Slug:', <?= json_encode($slug, JSON_UNESCAPED_UNICODE) ?>);
console.log('✅ [Clase] Cargada:', <?= json_encode(['id'=>$proyecto['id'],'nombre'=>$proyecto['nombre']]) ?>);
console.log('📚 [Clase] Áreas:', <?= count($areas) ?>);
console.log('🎓 [Clase] Competencias:', <?= count($competencias) ?>);
console.log('📦 [Clase] Kits:', <?= count($kits) ?>);
console.log('🎞️ [Clase] Recursos:', <?= count($recursos) ?>);
console.log('🏷️ [Clase] Tags:', <?= count($tags) ?>);
console.log('🔗 [Clase] Relacionadas:', <?= count($clases_relacionadas) ?>);
</script>
<?php include 'includes/footer.php'; ?>
