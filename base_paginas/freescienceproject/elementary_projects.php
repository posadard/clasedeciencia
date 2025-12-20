<?php
// Page-specific variables for header
$page_title = "Elementary Science Projects (Grades 4-6) - Free Science Project";
$page_description = "Elementary science projects for grades 4-6 (ages 10-14). Advanced experiments in electricity, physics, chemistry, biology, and meteorology with detailed instructions.";
$page_keywords = "elementary science projects, grades 4-6, middle school science, electricity experiments, physics projects, chemistry activities, biology studies, meteorology";
$canonical_url = "https://freescienceproject.com/elementary_projects.php";
$content_group1 = "Elementary Projects";
$content_group2 = "Grades 4-6";

// Breadcrumbs
$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Elementary Projects (Grades 4-6)']
];

// Schema.org markup
$schema_markup = json_encode([
  "@context" => "https://schema.org",
  "@type" => "CollectionPage",
  "name" => "Elementary Science Projects (Grades 4-6)",
  "description" => "Advanced science project ideas for elementary students grades 4-6, ages 10-14. Electricity, physics, chemistry, and biology experiments.",
  "url" => "https://freescienceproject.com/elementary_projects.php",
  "mainEntity" => [
    "@type" => "ItemList",
    "name" => "Elementary Science Projects",
    "numberOfItems" => 50
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
  <!-- Fixed Left Side Panel - Filters -->
  <aside class="filter-panel" id="filter-panel">
    <!-- Panel Header -->
    <div class="panel-header">
      <h2>Elementary Projects</h2>
      <p class="panel-subtitle">Grades 4-6 (ages 10-14)</p>
    </div>
    
    <!-- Panel Content -->
    <div class="panel-content">
      <!-- Search Box -->
      <div class="search-container">
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
          <div class="filter-option">
            <input type="checkbox" id="meteorology" name="subject" value="meteorology">
            <label for="meteorology">Meteorology</label>
          </div>
        </div>
      </div>

      <!-- Difficulty Filter -->
      <div class="filter-section">
        <h3>Difficulty</h3>
        <div class="filter-options">
          <div class="filter-option">
            <input type="checkbox" id="easy" name="difficulty" value="easy">
            <label for="easy">Easy</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="medium" name="difficulty" value="medium">
            <label for="medium">Medium</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="hard" name="difficulty" value="hard">
            <label for="hard">Hard</label>
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
            <input type="checkbox" id="electrical" name="materials" value="electrical">
            <label for="electrical">Electrical Parts</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="food" name="materials" value="food">
            <label for="food">Food Items</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="specialized" name="materials" value="specialized">
            <label for="specialized">Specialized Equipment</label>
          </div>
        </div>
      </div>

      <!-- Quick Access Filter -->
      <div class="filter-section">
        <h3>Quick Access</h3>
        <div class="filter-options">
          <div class="filter-option">
            <input type="checkbox" id="popular" name="quick" value="popular">
            <label for="popular">Popular Projects</label>
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
      <a href="/" class="back-link">← Back to Home</a>
    </div>
  </aside>

  <!-- Main Content Area -->
  <div class="main-content-area">
    <!-- Safety Notice -->
    <section class="safety-notice">
      <h4>Elementary Science Safety!</h4>
      <p>These elementary projects involve more complex experiments that may require adult supervision. Always read instructions carefully, have safety equipment ready, and ensure proper ventilation when needed. Some projects may involve electrical components or chemical reactions.</p>
    </section>

    <!-- Content Container -->
    <div class="content-container">
      <!-- Results Header -->
      <div class="results-header">
        <div class="results-count">
          <span id="results-count">Loading projects...</span>
        </div>
        <div class="sort-options">
          <select id="sort-select">
            <option value="">Sort by...</option>
            <option value="name">Sort by Title</option>
            <option value="subject">Sort by Subject</option>
            <option value="difficulty">Sort by Difficulty</option>
            <option value="popular">Popular First</option>
          </select>
        </div>
      </div>

      <!-- Projects Grid -->
      <div class="projects-grid" id="projects-container">
        <!-- Projects will be loaded here by JavaScript -->
      </div>

      <!-- No Results Message -->
      <div id="no-results" class="no-results" style="display: none;">
        <h3>No projects found</h3>
        <p>Try adjusting your filters or search terms to find more projects.</p>
      </div>
    </div>

    <!-- Continue Learning Section -->
    <div class="content-container">
      <h2>Continue Your Science Journey</h2>
      <p>Ready for more challenging experiments? Explore our other grade levels:</p>
      <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
        <a href="primary_projects.php" style="background: var(--accent-color, #34c759); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">← Primary Projects</a>
        <a href="intermediate_projects.php" style="background: var(--accent-color, #34c759); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">Intermediate Projects →</a>
      </div>
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
    console.log('Elementary Projects page loaded');
    console.log('searchUtils available:', typeof searchUtils !== 'undefined');
    console.log('CategoryPageManager available:', typeof CategoryPageManager !== 'undefined');
    
    // The CategoryPageManager will auto-initialize based on URL path
});
</script>
