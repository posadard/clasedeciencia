<?php
// Proyecto - Detalle del proyecto con guía
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

// Cargar guía (última versión)
$stmt = $pdo->prepare("SELECT * FROM guias WHERE clase_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$proyecto['id']]);
$guia = $stmt->fetch();

// Kit y componentes
$stmt = $pdo->prepare("SELECT id FROM kits WHERE clase_id = ? LIMIT 1");
$stmt->execute([$proyecto['id']]);
$kit = $stmt->fetch(PDO::FETCH_ASSOC);
$materiales = [];
if ($kit && isset($kit['id'])) {
    $stmt = $pdo->prepare("SELECT kc.*, i.nombre_comun, i.sku, i.unidad FROM kit_componentes kc JOIN kit_items i ON kc.item_id = i.id WHERE kc.kit_id = ? ORDER BY kc.sort_order ASC, i.nombre_comun ASC");
    $stmt->execute([(int)$kit['id']]);
    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Multimedia
$stmt = $pdo->prepare("SELECT * FROM recursos_multimedia WHERE clase_id = ? ORDER BY sort_order");
$stmt->execute([$proyecto['id']]);
$recursos = $stmt->fetchAll();

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
$schema_json = json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

include 'includes/header.php';
?>
<div class="container article-page">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <a href="/clases">Clases</a> / <strong><?= h($proyecto['nombre']) ?></strong>
    </div>
    <article>
        <header class="article-header">
            <h1><?= h($proyecto['nombre']) ?></h1>
            <div class="article-meta">
                <span class="section-badge">Ciclo <?= h($proyecto['ciclo']) ?></span>
                <span class="difficulty-badge"><?= h(ucfirst($proyecto['dificultad'])) ?></span>
                <span class="read-time">Duración: <?= (int)$proyecto['duracion_minutos'] ?> min</span>
            </div>
            <?php if (!empty($proyecto['imagen_portada'])): ?>
                <img src="<?= h($proyecto['imagen_portada']) ?>" alt="Portada de la clase" class="article-cover"/>
            <?php endif; ?>
            <?php if (!empty($proyecto['video_portada'])): ?>
                <div class="video-wrapper">
                    <iframe src="<?= h($proyecto['video_portada']) ?>" title="Video de la clase" allowfullscreen></iframe>
                </div>
            <?php endif; ?>
            <?php if (!empty($proyecto['resumen'])): ?>
                <p class="excerpt"><?= h($proyecto['resumen']) ?></p>
            <?php endif; ?>
        </header>

        <section class="article-content">
            <?php if ($guia): ?>
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
            <?php else: ?>
                <p>No hay guía disponible para esta clase.</p>
            <?php endif; ?>
        </section>

        <?php if (!empty($materiales)): ?>
        <section class="related-materials">
            <h2>Componentes del Kit</h2>
            <ul class="materials-list">
                <?php foreach ($materiales as $m): ?>
                    <li>
                        <?= h($m['nombre_comun']) ?>
                        <?php if (!empty($m['cantidad'])): ?>
                            <span class="badge"><?= h($m['cantidad']) ?></span>
                        <?php endif; ?>
                        <?php if (isset($m['es_incluido_kit']) && (int)$m['es_incluido_kit'] === 1): ?>
                            <span class="badge">Incluido</span>
                        <?php endif; ?>
                        <?php if (!empty($m['notas'])): ?>
                            <small><?= h($m['notas']) ?></small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <?php if (!empty($recursos)): ?>
        <section class="multimedia">
            <h2>Recursos Multimedia</h2>
            <div class="gallery">
                <?php foreach ($recursos as $r): ?>
                    <?php if ($r['tipo'] === 'imagen'): ?>
                        <img src="<?= h($r['url']) ?>" alt="<?= h($r['titulo'] ?? 'Imagen') ?>" />
                    <?php elseif ($r['tipo'] === 'video'): ?>
                        <div class="video-wrapper">
                            <iframe src="<?= h($r['url']) ?>" title="<?= h($r['titulo'] ?? 'Video') ?>" allowfullscreen></iframe>
                        </div>
                    <?php elseif ($r['tipo'] === 'pdf'): ?>
                        <a class="btn btn-secondary" href="<?= h($r['url']) ?>" target="_blank">Descargar PDF</a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </article>
</div>
<script>
console.log('🔍 [Clase] Slug:', '<?= h($slug) ?>');
console.log('✅ [Clase] Cargada clase:', <?= json_encode(['id'=>$proyecto['id'],'nombre'=>$proyecto['nombre']]) ?>);
console.log('📦 [Clase] Componentes del kit:', <?= count($materiales) ?>);
console.log('🎞️ [Clase] Recursos:', <?= count($recursos) ?>);
</script>
<?php include 'includes/footer.php'; ?>
