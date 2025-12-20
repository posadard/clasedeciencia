<?php
/**
 * Privacy Policy
 */
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$page_title = 'Privacy Policy';
$page_description = 'How we handle contact information and privacy at The Green Almanac.';
$canonical_url = SITE_URL . '/privacy.php';

include 'includes/header.php';
?>

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
