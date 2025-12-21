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
$canonical_url = SITE_URL . '/componente.php?slug=' . urlencode($material['slug']);

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

    <a href="/componentes?category=<?= urlencode($material['category_slug']) ?>" class="back-button">← Volver a <?= h($material['category_name']) ?></a>
</div>
<script>
console.log('🔍 [componente] Slug:', <?= json_encode($slug) ?>);
console.log('✅ [componente] Cargado:', <?= json_encode(['slug'=>$material['slug'],'nombre'=>$material['common_name']]) ?>);
</script>
<?php include 'includes/footer.php'; ?>
