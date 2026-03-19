<?php
/**
 * Sobre Nosotros — contenido gestionado desde Admin > Páginas
 */
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$pagina = false;
try {
    $pagina = cdc_get_pagina_estatica($pdo, 'sobre-nosotros');
} catch (Throwable $e) {
    error_log('sobre-nosotros.php DB error: ' . $e->getMessage());
}

if (!$pagina) {
    $page_title       = 'Sobre Nosotros';
    $page_description = 'Conoce el equipo y la misión de Clase de Ciencia.';
    $canonical_url    = SITE_URL . '/sobre-nosotros.php';
    include 'includes/header.php';
    echo '<div class="container"><div class="content-section"><h1>Sobre Nosotros</h1><p>Esta página no está disponible temporalmente. Escríbenos a <a href="mailto:' . CONTACT_EMAIL . '">' . CONTACT_EMAIL . '</a>.</p></div></div>';
    include 'includes/footer.php';
    exit;
}

$page_title       = $pagina['titulo'];
$page_description = $pagina['meta_description'] ?? 'Conoce el equipo y la misión de Clase de Ciencia.';
$canonical_url    = SITE_URL . '/sobre-nosotros.php';

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
console.log('🔍 [sobre-nosotros] Página cargada desde BD. updated_at: <?= h($pagina['updated_at']) ?>');
</script>
<?php include 'includes/footer.php'; ?>
