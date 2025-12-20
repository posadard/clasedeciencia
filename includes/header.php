<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? h($page_title) . ' - ' . SITE_NAME : SITE_NAME ?></title>
    <meta name="description" content="<?= isset($page_description) ? h($page_description) : h(SITE_DESCRIPTION) ?>">
    <?php if (isset($canonical_url)): ?>
    <link rel="canonical" href="<?= h($canonical_url) ?>">
    <?php endif; ?>
    
    <!-- OpenGraph -->
    <meta property="og:title" content="<?= isset($page_title) ? h($page_title) : SITE_NAME ?>">
    <meta property="og:description" content="<?= isset($page_description) ? h($page_description) : h(SITE_DESCRIPTION) ?>">
    <meta property="og:type" content="<?= isset($og_type) ? h($og_type) : 'website' ?>">
    <meta property="og:url" content="<?= isset($canonical_url) ? h($canonical_url) : h(SITE_URL) ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= isset($page_title) ? h($page_title) : SITE_NAME ?>">
    <meta name="twitter:description" content="<?= isset($page_description) ? h($page_description) : h(SITE_DESCRIPTION) ?>">
    
    <!-- Google Fonts - Tipografía moderna científica -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/article-content.css">
    <link rel="stylesheet" href="/assets/css/print.css" media="print">
    <!-- Favicon and touch icon -->
    <link rel="icon" type="image/svg+xml" href="/assets/icons/favicon.svg">
    <link rel="alternate icon" href="/assets/icons/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/assets/icons/favicon.svg">
    <meta name="theme-color" content="#4a7c59">
    
    <!-- Schema.org: Global Organization -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?= SITE_NAME ?>",
        "url": "<?= SITE_URL ?>",
        "logo": "<?= SITE_URL ?>/assets/icons/favicon.svg",
        "description": "<?= h(SITE_DESCRIPTION) ?>",
        "sameAs": [
            "<?= CHEMICALSTORE_URL ?>"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "email": "<?= CONTACT_EMAIL ?>",
            "contactType": "Customer Service"
        }
    }
    </script>
    
    <?php if (isset($schema_json)): ?>
    <!-- Schema.org: Page-specific schema -->
    <script type="application/ld+json">
    <?= $schema_json ?>
    </script>
    <?php endif; ?>
</head>
<body class="<?= isset($body_class) ? h($body_class) : '' ?>">
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <h1 class="site-title">
                    <a href="/">
                        <svg class="logo-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 0.3rem;">
                            <!-- Lupa (magnifying glass) -->
                            <circle cx="10" cy="10" r="7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <line x1="15" y1="15" x2="21" y2="21" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                            <circle cx="10" cy="10" r="4" fill="none" stroke="currentColor" stroke-width="0.8" opacity="0.3"/>
                            <ellipse cx="8" cy="8" rx="2" ry="3" fill="currentColor" opacity="0.15" transform="rotate(-35 8 8)"/>
                        </svg>
                        <?= SITE_NAME ?>
                    </a>
                </h1>
                <nav class="main-nav" aria-label="Main Navigation">
                    <ul>
                        <li><a href="/">Inicio</a></li>
                        <li><a href="/clases">Clases</a></li>
                        <li><a href="/contact.php">Contacto</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- Search in header - Visible en desktop, oculto en mobile -->
        <div class="container header-search-container">
            <div class="search-box">
                <form class="search-form" role="search" aria-label="Buscar clases de ciencia">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input 
                        type="search" 
                        class="search-input" 
                        placeholder="Buscar por tema, área, grado, ciclo..." 
                        aria-label="Buscar clases"
                        autocomplete="off"
                    >
                </form>
            </div>
        </div>
    </header>
    
    <main class="site-main">
