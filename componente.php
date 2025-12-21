<?php
// Página de detalle de Componente (kit_items)
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/materials-functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug === '') {
    header('HTTP/1.1 302 Found');
    header('Location: /componentes');
    exit;
}

$material = get_material_by_slug($pdo, $slug);
if (!$material) {
    header('HTTP/1.0 404 Not Found');
    $page_title = 'Componente no encontrado';
    $page_description = 'El componente solicitado no existe.';
    include 'includes/header.php';
    echo '<div class="container"><h1>Componente no encontrado</h1><p>El componente solicitado no existe.</p></div>';
    include 'includes/footer.php';
    exit;
}

$page_title = $material['common_name'];
$page_description = generate_excerpt($material['description'] ?? '', 160);
$canonical_url = SITE_URL . '/' . urlencode($material['slug']);

include 'includes/header.php';
?>
<div class="container material-detail">
    <div class="breadcrumb">
        <a href="/">Inicio</a> /
        <a href="/componentes">Componentes</a> /
        <?php if (!empty($material['category_slug'])): ?>
            <a href="/componentes?category=<?= urlencode($material['category_slug']) ?>"><?= h($material['category_name']) ?></a> /
        <?php endif; ?>
        <strong><?= h($material['common_name']) ?></strong>
    </div>

    <div class="material-top">
        <div class="material-main">
            <h1><?= h($material['common_name']) ?></h1>
            <table class="material-specs" aria-labelledby="material-specs-title">
                <caption id="material-specs-title" class="sr-only">Datos del componente <?= h($material['common_name']) ?></caption>
                <tbody>
                    <tr>
                        <th scope="row">Categoría</th>
                        <td><?= h($material['category_name']) ?></td>
                    </tr>
                    <?php if (!empty($material['slug'])): ?>
                    <tr>
                        <th scope="row">SKU</th>
                        <td class="mono"><?= h($material['slug']) ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <aside class="product-card">
            <div class="product-badges">
                <span class="material-badge">Componente</span>
                <?php if (!empty($material['category_name'])): ?><span class="material-badge" style="background:#e9ecef;color:#333"><?= h($material['category_name']) ?></span><?php endif; ?>
            </div>
            <div class="product-image">
                <div class="product-image-fallback" aria-hidden="true">📦 <?= h($material['category_name'] ?? '') ?></div>
            </div>
        </aside>
    </div>

    <?php if (!empty($material['description'])): ?>
    <div class="content-section">
        <h2>Descripción / Seguridad</h2>
        <p><?= nl2br(h($material['description'])) ?></p>
    </div>
    <?php endif; ?>

    <?php
    // Kits que incluyen este componente
    $kits_rel = [];
    try {
        $stmtK = $pdo->prepare("SELECT k.id, k.nombre, k.slug, k.codigo, k.version, k.updated_at
                                 FROM kit_componentes kc
                                 JOIN kits k ON k.id = kc.kit_id
                                 WHERE kc.item_id = ? AND k.activo = 1
                                 ORDER BY k.updated_at DESC, k.id DESC");
        $stmtK->execute([(int)$material['id']]);
        $kits_rel = $stmtK->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log('Error componente.kits: ' . $e->getMessage());
        $kits_rel = [];
    }

    // Clases asociadas a esos kits
    $clases_rel = [];
    if (!empty($kits_rel)) {
        $kitIds = array_column($kits_rel, 'id');
        $ph = implode(',', array_fill(0, count($kitIds), '?'));
        try {
            $sqlC = "SELECT DISTINCT c.*
                     FROM clases c
                     JOIN clase_kits ck ON ck.clase_id = c.id
                     WHERE ck.kit_id IN ($ph) AND c.activo = 1
                     ORDER BY c.destacado DESC, c.updated_at DESC";
            $stmtC = $pdo->prepare($sqlC);
            $stmtC->execute($kitIds);
            $clases_rel = $stmtC->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error componente.clases: ' . $e->getMessage());
            $clases_rel = [];
        }
    }
    ?>

    <?php if (!empty($kits_rel)): ?>
    <section class="kit-uses">
        <h2>🧰 Kits que incluyen este componente</h2>
        <div class="articles-grid">
            <?php foreach ($kits_rel as $k): ?>
            <article class="article-card" data-href="/<?= h($k['slug']) ?>">
                <a class="card-link" href="/<?= h($k['slug']) ?>">
                    <div class="card-content">
                        <div class="card-meta">
                            <span class="section-badge">Kit</span>
                            <?php if (!empty($k['codigo'])): ?>
                            <span class="difficulty-badge">Código: <?= h($k['codigo']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3><?= h($k['nombre']) ?>
                            <span title="Ver kit" aria-label="Ver kit" style="margin-left:6px">🔎</span>
                        </h3>
                        <div class="card-footer">
                            <?php if (!empty($k['version'])): ?><span class="area">Versión <?= h($k['version']) ?></span><?php endif; ?>
                        </div>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($clases_rel)): ?>
    <section class="related-classes">
        <h2>📚 Clases asociadas</h2>
        <div class="related-grid">
            <?php foreach ($clases_rel as $c): ?>
                <a href="/clase.php?slug=<?= h($c['slug']) ?>" class="related-card">
                    <?php if (!empty($c['imagen_portada'])): ?>
                        <img src="<?= h($c['imagen_portada']) ?>" alt="<?= h($c['nombre']) ?>" class="related-thumbnail" />
                    <?php endif; ?>
                    <div class="related-info">
                        <h4><?= h($c['nombre']) ?></h4>
                        <div class="related-meta">
                            <span class="badge">Ciclo <?= h($c['ciclo']) ?></span>
                            <span class="badge"><?= h(ucfirst($c['dificultad'] ?? '')) ?></span>
                        </div>
                        <?php if (!empty($c['resumen'])): ?>
                            <p class="related-excerpt"><?= h(mb_substr($c['resumen'], 0, 100)) ?>...</p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <a href="/componentes?category=<?= urlencode($material['category_slug']) ?>" class="back-button">← Volver a <?= h($material['category_name']) ?></a>
</div>
<script>
console.log('🔍 [componente] Slug:', <?= json_encode($slug) ?>);
console.log('✅ [componente] Cargado:', <?= json_encode(['slug'=>$material['slug'],'nombre'=>$material['common_name']]) ?>);
console.log('🧰 [componente] Kits relacionados:', <?= isset($kits_rel) ? count($kits_rel) : 0 ?>);
console.log('📚 [componente] Clases relacionadas:', <?= isset($clases_rel) ? count($clases_rel) : 0 ?>);
</script>
<?php include 'includes/footer.php'; ?>
