<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Dynamic title and meta tags will be inserted here by each page -->
<?php if (!isset($page_title)) $page_title = "Free Science Fair Projects - Educational STEM Experiments for K-12 Students"; ?>
<?php if (!isset($page_description)) $page_description = "Discover 1000+ free science fair project ideas and step-by-step experiments for students grades K-12. From physics and chemistry to biology and engineering - complete with materials lists, safety guidelines, and educational kits."; ?>
<?php if (!isset($page_keywords)) $page_keywords = "science fair projects, STEM education, science experiments for kids, physics projects, chemistry experiments, biology activities, elementary science, middle school projects, high school research, educational science kits"; ?>
<?php if (!isset($canonical_url)) $canonical_url = "https://freescienceproject.com" . $_SERVER['REQUEST_URI']; ?>

<title><?php echo $page_title; ?></title>
<meta name="description" content="<?php echo $page_description; ?>">
<meta name="keywords" content="<?php echo $page_keywords; ?>">
<meta name="author" content="Free Science Project">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo $canonical_url; ?>">
<meta property="og:title" content="<?php echo $page_title; ?>">
<meta property="og:description" content="<?php echo $page_description; ?>">
<meta property="og:image" content="https://freescienceproject.com/images/freescienceproject02.jpg">
<meta property="og:site_name" content="Free Science Project">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?php echo $canonical_url; ?>">
<meta property="twitter:title" content="<?php echo $page_title; ?>">
<meta property="twitter:description" content="<?php echo $page_description; ?>">
<meta property="twitter:image" content="https://freescienceproject.com/images/freescienceproject02.jpg">

<!-- Canonical URL -->
<link rel="canonical" href="<?php echo $canonical_url; ?>">

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

<!-- Preconnect to external domains -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://www.google-analytics.com">

<!-- CSS -->
<link rel="stylesheet" href="/css/modern-styles.css?v=<?php echo filemtime(__DIR__ . '/../css/modern-styles.css'); ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Additional page-specific CSS -->
<?php if (isset($additional_css) && is_array($additional_css)): ?>
<?php foreach ($additional_css as $css_file): ?>
<link rel="stylesheet" href="<?php echo $css_file; ?>">
<?php endforeach; ?>
<?php endif; ?>

<!-- AbanteCart Shop Widget CSS -->
<link rel="stylesheet" href="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css">

<!-- JavaScript -->
<script src="/js/project-shop-categories.js?v=<?php echo filemtime(__DIR__ . '/../js/project-shop-categories.js'); ?>" defer></script>

<!-- AbanteCart Shop Widget Script -->
<script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>

<!-- Dynamic Schema.org JSON-LD will be inserted here by each page -->
<?php if (isset($schema_markup)): ?>
<script type="application/ld+json">
<?php echo $schema_markup; ?>
</script>
<?php endif; ?>

<!-- Base Organization Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": "Free Science Project",
  "url": "https://freescienceproject.com",
  "logo": "https://freescienceproject.com/images/freescienceproject02.jpg",
  "description": "Free science fair project ideas and educational science kits for students of all ages (K-12). Comprehensive STEM education resources covering physics, chemistry, biology, and engineering experiments for elementary, middle school, and high school students.",
  "foundingDate": "2005",
  "knowsAbout": ["Physics", "Chemistry", "Biology", "Earth Science", "Engineering", "STEM Education", "Science Fair Projects"],
  "keywords": "K-12 education, elementary science, middle school projects, high school experiments, STEM learning, science fair, educational resources, physics experiments, chemistry projects, biology activities, earth science studies, engineering projects, primary science projects, intermediate science experiments, senior science fair ideas, household materials experiments, craft science projects, food science activities, easy science experiments, medium difficulty projects, hard science challenges, magnetism experiments, electricity projects, crystal growing, plant biology, space science, solar system projects, invisible ink chemistry, combustion experiments, nutrition studies, anatomy models, habitat studies, tooth decay experiments, phototropism, germination projects, magnetic field visualization, compass making, bird house construction, insect collection, human eye vision, heart model, scientific method, hands-on learning",
  "areaServed": {
    "@type": "Place",
    "name": "Global K-12 Education Market"
  },
  "address": {
    "@type": "PostalAddress",
    "addressCountry": "US",
    "addressRegion": "NJ"
  },
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+1-973-773-7355",
    "contactType": "customer service",
    "availableLanguage": "English"
  },
  "sameAs": [
    "https://www.scienceproject.com",
    "https://www.kidslovekits.com"
  ]
}
</script>

<!-- Website Schema: Provided by individual pages when needed -->

<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID', {
    page_title: '<?php echo addslashes($page_title ?? ""); ?>',
    page_location: window.location.href,
    content_group1: '<?php echo isset($content_group1) ? addslashes($content_group1) : "General"; ?>',
    content_group2: '<?php echo isset($content_group2) ? addslashes($content_group2) : "All Grades"; ?>'
  });
</script>
</head>

<body<?php 
// Add page-specific body classes
$bodyClasses = [];
if (basename($_SERVER['PHP_SELF']) === 'index.php' && $_SERVER['REQUEST_URI'] === '/') {
    $bodyClasses[] = 'homepage';
}
// Add class for individual project pages
if (strpos($_SERVER['REQUEST_URI'], '/projects/') !== false && 
    $_SERVER['REQUEST_URI'] !== '/projects/' && 
    !strpos($_SERVER['REQUEST_URI'], '/projects/index.php')) {
    $bodyClasses[] = 'project-page';
}
if (!empty($bodyClasses)) {
    echo ' class="' . implode(' ', $bodyClasses) . '"';
}
?>>
  <!-- Navigation -->
  <nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="container">
      <div class="nav-container">
        <div class="logo">
          <a href="/">
            <img src="/images/freescienceproject02.jpg" alt="Free Science Project - Educational STEM Resources" width="200" height="42" loading="eager">
          </a>
        </div>
        <ul class="nav-links">
          <li><a href="/" <?php if (basename($_SERVER['PHP_SELF']) == 'index.php' || $_SERVER['REQUEST_URI'] == '/') echo 'aria-current="page"'; ?>>Home</a></li>
          <li><a href="/primary_projects.php" <?php if (basename($_SERVER['PHP_SELF']) == 'primary_projects.php') echo 'aria-current="page"'; ?>>Primary (K-4)</a></li>
          <li><a href="/elementary_projects.php" <?php if (basename($_SERVER['PHP_SELF']) == 'elementary_projects.php') echo 'aria-current="page"'; ?>>Elementary (4-6)</a></li>
          <li><a href="/intermediate_projects.php" <?php if (basename($_SERVER['PHP_SELF']) == 'intermediate_projects.php') echo 'aria-current="page"'; ?>>Intermediate (7-8)</a></li>
          <li><a href="/senior_projects.php" <?php if (basename($_SERVER['PHP_SELF']) == 'senior_projects.php') echo 'aria-current="page"'; ?>>Senior (9-12)</a></li>
          <li><a href="/projects/">All Projects</a></li>
        </ul>
      </div>
      
      <!-- Breadcrumb Navigation integrated within navbar -->
      <?php if (isset($breadcrumbs) && count($breadcrumbs) > 1): ?>
      <div class="breadcrumb-container">
        <div class="container">
          <nav class="breadcrumb" aria-label="Breadcrumb">
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
              <?php if ($index > 0): ?> > <?php endif; ?>
              <?php if (isset($crumb['url']) && $index < count($breadcrumbs) - 1): ?>
                <a href="<?php echo $crumb['url']; ?>"><?php echo $crumb['name']; ?></a>
              <?php else: ?>
                <span><?php echo $crumb['name']; ?></span>
              <?php endif; ?>
            <?php endforeach; ?>
          </nav>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Main content wrapper -->
  <main id="main-content" role="main">
  
  <!-- End of header include -->