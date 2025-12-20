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
                    <a href="/"><?= SITE_NAME ?></a>
                </h1>
                <nav class="main-nav" aria-label="Main Navigation">
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/library.php">Library</a></li>
                        <li><a href="/materials.php">Materials</a></li>
                        <?php
                        $sections = get_sections($pdo);
                        foreach ($sections as $nav_section):
                        ?>
                        <li><a href="/section.php?slug=<?= h($nav_section['slug']) ?>"><?= h($nav_section['name']) ?></a></li>
                        <?php endforeach; ?>
                        <li><a href="/contact.php">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- Global search (AJAX) placed below the nav for full-width centered bar -->
        <div class="container">
            <form id="global-search-form" class="global-search" role="search" action="/search.php" method="get" autocomplete="off">
                <div class="search-wrapper">
                    <label for="global-search-input" class="sr-only">Search the site</label>
                    <input id="global-search-input" name="q" type="search" placeholder="Search articles, materials..." aria-label="Search the site" />
                    <div class="search-spinner" id="global-search-spinner" aria-hidden="true" role="status" aria-label="Searching"></div>
                    <div id="global-search-results" class="search-results" role="listbox" aria-expanded="false" aria-hidden="true"></div>
                </div>
            </form>
        </div>
    </header>
    
    <main class="site-main">
