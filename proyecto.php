<?php
// Proyecto - Detalle del proyecto con guía
require_once 'config.php';
require_once 'includes/functions.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (!$slug) {
    header('Location: /catalogo.php');
    exit;
}

// Cargar proyecto
$stmt = $pdo->prepare("SELECT * FROM proyectos WHERE slug = ? AND activo = 1");
$stmt->execute([$slug]);
$proyecto = $stmt->fetch();
if (!$proyecto) {
    header('Location: /catalogo.php');
    exit;
}

// Cargar guía activa
$stmt = $pdo->prepare("SELECT * FROM guias WHERE proyecto_id = ? AND activa = 1 ORDER BY id DESC LIMIT 1");
$stmt->execute([$proyecto['id']]);
$guia = $stmt->fetch();

// Materiales (puente)
$stmt = $pdo->prepare("SELECT pm.*, m.nombre_comun, m.slug AS material_slug FROM proyecto_materiales pm JOIN materiales m ON pm.material_id = m.id WHERE pm.proyecto_id = ?");
$stmt->execute([$proyecto['id']]);
$materiales = $stmt->fetchAll();

// Multimedia
$stmt = $pdo->prepare("SELECT * FROM recursos_multimedia WHERE proyecto_id = ? ORDER BY orden");
$stmt->execute([$proyecto['id']]);
$recursos = $stmt->fetchAll();

$page_title = $proyecto['seo_title'] ?: ($proyecto['nombre'] . ' - Proyecto Científico');
$page_description = $proyecto['seo_description'] ?: ($proyecto['resumen'] ?: 'Guía interactiva del proyecto científico');
$canonical_url = $proyecto['canonical_url'] ?: (SITE_URL . '/proyecto.php?slug=' . $proyecto['slug']);

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
        <a href="/">Inicio</a> / <a href="/catalogo.php">Catálogo</a> / <strong><?= h($proyecto['nombre']) ?></strong>
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
                <img src="<?= h($proyecto['imagen_portada']) ?>" alt="Portada del proyecto" class="article-cover"/>
            <?php endif; ?>
            <?php if (!empty($proyecto['video_portada'])): ?>
                <div class="video-wrapper">
                    <iframe src="<?= h($proyecto['video_portada']) ?>" title="Video del proyecto" allowfullscreen></iframe>
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
                <p>No hay guía activa para este proyecto.</p>
            <?php endif; ?>
        </section>

        <?php if (!empty($materiales)): ?>
        <section class="related-materials">
            <h2>Materiales del Proyecto</h2>
            <ul class="materials-list">
                <?php foreach ($materiales as $m): ?>
                    <li>
                        <a href="/material.php?slug=<?= h($m['material_slug']) ?>"><?= h($m['nombre_comun']) ?></a>
                        <?php if (!empty($m['cantidad'])): ?>
                            <span class="badge"><?= h($m['cantidad']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($m['es_incluido_kit'])): ?>
                            <span class="badge">Incluido en kit</span>
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
console.log('🔍 [proyecto] Slug:', '<?= h($slug) ?>');
console.log('✅ [proyecto] Cargado proyecto:', <?= json_encode(['id'=>$proyecto['id'],'nombre'=>$proyecto['nombre']]) ?>);
console.log('📦 [proyecto] Materiales:', <?= count($materiales) ?>);
console.log('🎞️ [proyecto] Recursos:', <?= count($recursos) ?>);
</script>
<?php include 'includes/footer.php'; ?>
