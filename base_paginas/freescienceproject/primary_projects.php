<?php
// Page-specific variables for header
$page_title = "Primary Science Projects (Grades 1-4) - Free Science Project";
$page_description = "Primary science projects for grades 1-4 (ages 6-10). Simple, safe experiments perfect for young students including volcano models, magnets, plants, and basic chemistry.";
$page_keywords = "primary science projects, grades 1-4, elementary science, kids science experiments, young students, volcano model, magnet experiments, plant growth";
$canonical_url = "https://freescienceproject.com/primary_projects.php";
$content_group1 = "Primary Projects";
$content_group2 = "Grades K-4";

// Breadcrumbs
$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Primary Projects (Grades 1-4)']
];

// Schema.org markup
$schema_markup = json_encode([
  "@context" => "https://schema.org",
  "@type" => "CollectionPage",
  "name" => "Primary Science Projects (Grades 1-4)",
  "description" => "Science project ideas perfect for primary students grades 1-4, ages 6-10. Simple, safe experiments with easy-to-find materials.",
  "url" => "https://freescienceproject.com/primary_projects.htm",
  "mainEntity" => [
    "@type" => "ItemList",
    "name" => "Primary Science Projects",
    "description" => "Collection of science experiments suitable for elementary students grades 1-4"
  ],
  "breadcrumb" => [
    "@type" => "BreadcrumbList",
    "itemListElement" => [
      [
        "@type" => "ListItem",
        "position" => 1,
        "name" => "Home",
        "item" => "https://freescienceproject.com"
      ],
      [
        "@type" => "ListItem",
        "position" => 2,
        "name" => "Primary Projects",
        "item" => "https://freescienceproject.com/primary_projects.php"
      ]
    ]
  ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Include header
include_once __DIR__ . '/includes/header.php';
?>

<!-- Mobile Filter Toggle -->
<button class="mobile-filter-toggle" onclick="toggleMobileFilters()" aria-label="Toggle Filters">
  <span>⚡</span>
</button>

<div class="page-layout">
  <1-- Fixed Left Side Panel - Filters -->
  <aside class="filter-panel" id="filter-panel">
    <!-- Panel Header -->
 <div class="panel-header">
      <h2>Primary Projects</h2>
      <p class="panel-subtitle">Grades 1-4 (ages 6-10)</p>
    </div>
    
    <!-- Panel Content -->
    <div class="panel-content">
      <!-- Search Box -->
      <div class="search-contqiner">
        <input type="text" id="search-projects" class="search-box" placeholder="Search projects...">
      </div>
      
      <!-- Subject Filter -->
      <div class="filter-section">
        <h3>Subject</h3>
        <div class="filter-options">
          <div class="filter-option">
            <input type="checkbox" id="physics" name="subject" value="physics">
            <label for="physics">Physics</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="chemistry" name="subject" value="chemistry">
            <label for="chemistry">Chemistry</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="biology" name="subject" value="biology">
            <label for="biology">Biology</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="earth-science" name="subject" value="earth-science">
            <label for="earth-science">Earth Science</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="space" name="subject" value="space">
            <label for="space">Space Science</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="engineering" name="subject" value="engineering">
            <label for="engineering">Engineering</label>
          </div>
        </div>
      </div>

      <!-- Difficulty Filter -->
      <div class="filter-section">
        <h3>Difficulty</h3>
        <div class="filter-options">
          <div class="filter-option">
            <input type="checkbox" id="easy" name="difficulty" value="easy">
            <label for="easy">Easy (Quick)</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="medium" name="difficulty" value="medium">
            <label for="medium">Medium</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="advanced" name="difficulty" value="advanced">
            <label for="advanced">Advanced</label>
          </div>
        </div>
      </div>

      <!-- Materials Filter -->
      <div class="filter-section">
        <h3>Materials</h3>
        <div class="filter-options">
          <div class="filter-option">
            <input type="checkbox" id="household" name="materials" value="household">
            <label for="household">Household Items</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="craft" name="materials" value="craft">
            <label for="craft">Craft Supplies</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="food" name="materials" value="food">
            <label for="food">Food Items</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="electrical" name="materials" value="electrical">
            <label for="electrical">Electrical Components</label>
          </div>
        </div>
      </div>

      <!-- Quick Access -->
      <div class="filter-section">
        <h3>Quick Access</h3>
        <div class="filter-options">
          <div class="filter-option">
            <input type="checkbox" id="popular" name="quick" value="popular">
            <label for="popular">Most Popular</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="new" name="quick" value="new">
            <label for="new">Recently Added</label>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel Actions -->
    <div class="panel-actions">
      <button class="clear-filters" onclick="clearAllFilters()">Clear All Filters</button>
      <a href="index.php" class="back-link">← Back to Home</a>
    </div>
  </aside>

  <!-- Main Content Area -->
  <div class="main-content-area">
    <!-- Safety Notice -->
    <section class="safety-notice">
      <h4>Safety First!</h4>
      <p>Always have an adult help you with science experiments. Read all instructions carefully before starting, and make sure you have all the materials you need. Have fun and stay safe!</p>
    </section>

    <!-- Content Container -->
    <div class="content-container">
      <div class="results-header">
        <div class="results-count">
          <span id="results-count">Showing 48 projects</span>
        </div>
        <div class="sort-options">
          <select id="sort-select">
            <option value="name">Sort by Name</option>
            <option value="difficulty">Sort by Difficulty</option>
            <option value="subject">Sort by Subject</option>
            <option value="popular">Most Popular</option>
          </select>
        </div>
      </div>
 
      <div class="projects-grid" id="projects-container">
        <!-- Projects will be populated by JavaScript -->
      </div>

      <!-- No results message -->
      <div class="no-results" id="no-results" style="display: none;">
        <h3>No projects found</h3>
        <p>Try adjusting your filters or search terms to find more projects.</p>
        <button class="clear-filters" onclick="clearAllFilters()">Clear All Filters</button>
      </div>

      <!-- Call to Action Section -->
      <section style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 2rem; border-radius: 12px; margin: 3rem 0 2rem 0; text-align: center;">
        <h3 style="color: var(--primary-color, #2c5aa0); margin-bottom: 1rem;">Ready to Start Your Science Adventure?</h3>
        <p style="margin-bottom: 2rem; color: #666;">Choose a project that interests you and start exploring the wonderful world of science!</p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
          <a href="elementary_projects.php" style="background: var(--accent-color, #34c759); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">Elementary Projects →</a>
          <a href="index.php" style="background: var(--primary-color, #2c5aa0); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">← Back to Home</a>
        </div>
      </section>

      <!-- Safety Notice -->
    </div>
  </div>
</div>

</main>

<?php include_once __DIR__ . '/includes/section-footer.php'; ?>


    

<!-- Enhanced Search Scripts -->
<script src="/js/science-projects-data.js?v=<?php echo filemtime(__DIR__ . '/js/science-projects-data.js'); ?>"></script>
<script src="/js/category-page.js?v=<?php echo filemtime(__DIR__ . '/js/category-page.js'); ?>"></script>

<script>
// Debug: Check if scripts are loading correctly
document.addEventListener('DOMContentLoaded', function() {
    console.log('Primary Projects page loaded');
    console.log('searchUtils available:', typeof searchUtils !== 'undefined');
    console.log('CategoryPageManager available:', typeof CategoryPageManager !== 'undefined');
    
    // The CategoryPageManager will auto-initialize based on URL path
});
</script>

