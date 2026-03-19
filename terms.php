<?php
/**
 * Términos de Uso — contenido gestionado desde Admin > Páginas
 */
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$pagina = false;
try {
    $pagina = cdc_get_pagina_estatica($pdo, 'terms');
} catch (Throwable $e) {
    error_log('terms.php DB error: ' . $e->getMessage());
}

if (!$pagina) {
    $page_title       = 'Términos de Uso';
    $page_description = 'Términos y condiciones de uso de Clase de Ciencia.';
    $canonical_url    = SITE_URL . '/terms.php';
    include 'includes/header.php';
    echo '<div class="container"><div class="content-section"><h1>Términos de Uso</h1><p>Esta página no está disponible temporalmente. Escríbenos a <a href="mailto:' . CONTACT_EMAIL . '">' . CONTACT_EMAIL . '</a>.</p></div></div>';
    include 'includes/footer.php';
    exit;
}

$page_title       = $pagina['titulo'];
$page_description = $pagina['meta_description'] ?? 'Términos y condiciones de uso de Clase de Ciencia.';
$canonical_url    = SITE_URL . '/terms.php';

include 'includes/header.php';
?>
<div class="container">
    <div class="breadcrumb">
        <a href="/">Inicio</a> / <strong><?= h($pagina['titulo']) ?></strong>
    </div>

    <h1><?= h($pagina['titulo']) ?></h1>

    <div class="content-section">
        <?= $pagina['contenido_html'] /* HTML de confianza editado por Admin */ ?>

        <p class="text-muted" style="margin-top:2rem;"><small>
            Última actualización: <?= date('d/m/Y', strtotime($pagina['updated_at'])) ?>
        </small></p>
    </div>
</div>
<script>
console.log('🔍 [terms] Página cargada desde BD. updated_at: <?= h($pagina['updated_at']) ?>');
</script>
<?php include 'includes/footer.php'; ?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">Home</a> / <strong>Terms of Use</strong>
    </div>

    <h1>Terms of Use</h1>

    <div class="content-section">
        <p>
            The Green Almanac provides general information related to practical chemistry for homesteads and workshop projects.
            Content is provided for informational and educational purposes.
        </p>

        <h2>Not professional advice</h2>
        <p>
            None of the articles or materials on this site should be construed as professional, medical, legal, or commercial advice.
            The ideas and methods presented are publicly available information and may require adaptation to your specific context.
            You are responsible for your own safety and legal compliance.
        </p>

        <h2>Contact and responses</h2>
        <p>
            If you contact us, we will make reasonable efforts to reply. We do not guarantee specific response times, but we value
            communication and will aim to respond within a reasonable timeframe.
        </p>

        <h2>Limitation of liability</h2>
        <p>
            To the extent permitted by law, The Green Almanac and its contributors are not liable for direct, indirect, or
            consequential damages arising from the use of information published on this site.
        </p>

        <p class="text-muted"><small>
            Última actualización: <?= date('F j, Y') ?>
        </small></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
