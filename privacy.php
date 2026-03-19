<?php
/**
 * Política de Privacidad — contenido gestionado desde Admin > Páginas
 */
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$pagina = false;
try {
    $pagina = cdc_get_pagina_estatica($pdo, 'privacy');
} catch (Throwable $e) {
    error_log('privacy.php DB error: ' . $e->getMessage());
}

if (!$pagina) {
    $page_title       = 'Política de Privacidad';
    $page_description = 'Política de privacidad de Clase de Ciencia.';
    $canonical_url    = SITE_URL . '/privacy.php';
    include 'includes/header.php';
    echo '<div class="container"><div class="content-section"><h1>Política de Privacidad</h1><p>Esta página no está disponible temporalmente. Escríbenos a <a href="mailto:' . CONTACT_EMAIL . '">' . CONTACT_EMAIL . '</a>.</p></div></div>';
    include 'includes/footer.php';
    exit;
}

$page_title       = $pagina['titulo'];
$page_description = $pagina['meta_description'] ?? 'Política de privacidad de Clase de Ciencia.';
$canonical_url    = SITE_URL . '/privacy.php';

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
console.log('🔍 [privacy] Página cargada desde BD. updated_at: <?= h($pagina['updated_at']) ?>');
</script>
<?php include 'includes/footer.php'; ?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">Home</a> / <strong>Privacy Policy</strong>
    </div>

    <h1>Privacy Policy</h1>

    <div class="content-section">
        <p>
            En The Green Almanac respetamos su privacidad. Si usted nos contacta usando el formulario de contacto o
            por correo, usamos la información proporcionada únicamente para responder a su consulta y ofrecer
            asistencia relacionada con la comunicación solicitada.
        </p>

        <h2>Use of contact information</h2>
        <p>
            Contact details you provide (for example your name and email address) are used to respond to your inquiry.
            We do not share contact information with third parties without your explicit consent, except where required by law.
        </p>

        <h2>Third-party links</h2>
        <p>
            Our site may contain links to external websites (for example, ChemicalStore). We are not responsible for the
            privacy practices of those sites. Please review the privacy policies of any external services before submitting
            personal information.
        </p>

        <h2>Cookies and analytics</h2>
        <p>
            We may use cookies and analytics tools to improve the site. Collected data is anonymized and used for
            statistical purposes and content improvement.
        </p>

        <h2>Contact</h2>
        <p>
            If you contact us, we will make a reasonable effort to reply. For inquiries use <a href="/contact.php">the contact page</a>
            or email office@chemicalstore.com.
        </p>

        <p class="text-muted"><small>
            Última actualización: <?= date('F j, Y') ?>
        </small></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
