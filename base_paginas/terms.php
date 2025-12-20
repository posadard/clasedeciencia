<?php
/**
 * Terms of Use
 */
require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$page_title = 'Terms of Use';
$page_description = 'Terms of use for The Green Almanac - general information and limitations of liability.';
$canonical_url = SITE_URL . '/terms.php';

include 'includes/header.php';
?>

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
