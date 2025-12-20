<?php
/**
 * Contact Page
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db-functions.php';

$page_title = 'Contact Us';
$page_description = 'Get in touch with The Green Almanac team';
$canonical_url = SITE_URL . '/contact.php';

include 'includes/header.php';
?>

<div class="container contact-page">
    <header class="page-header">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you</p>
    </header>
    
    <div class="contact-content">
        <section class="contact-section">
            <h2>Get in Touch</h2>
            <p>For questions, suggestions, or article submissions, please email us:</p>
            <p class="contact-email">
                <a href="mailto:<?= h(CONTACT_EMAIL) ?>?subject=Green%20Almanac%20Inquiry"><?= h(CONTACT_EMAIL) ?></a>
            </p>
        </section>
        
        <section class="contact-section">
            <h2>Topics We Cover</h2>
            <ul>
                <li>Seasonal homestead chemistry projects</li>
                <li>Farm and garden chemical applications</li>
                <li>Home and workshop chemistry</li>
                <li>Traditional methods and recipes</li>
                <li>Safety and best practices</li>
            </ul>
        </section>
        
        <section class="contact-section">
            <h2>Contribute</h2>
            <p>We welcome article submissions from experienced homesteaders and practitioners. If you have a chemistry-related project or method to share, please reach out with:</p>
            <ul>
                <li>Brief description of your topic</li>
                <li>Your background/experience</li>
                <li>Any supporting photos or materials</li>
            </ul>
        </section>
        
        <section class="contact-section">
            <h2>Partner Sites</h2>
            <p><strong>ChemicalStore.com</strong> - For product inquiries and orders, visit <a href="<?= h(CHEMICALSTORE_URL) ?>" target="_blank" rel="noopener">ChemicalStore.com</a> or email <?= h(CONTACT_EMAIL) ?>.</p>
            <p><strong>Safety Data Sheets</strong> - For SDS requests, visit <a href="<?= h(SDS_URL) ?>" target="_blank" rel="noopener">sds.chemicalstore.com</a>.</p>
        </section>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
