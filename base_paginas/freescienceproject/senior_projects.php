<?php
// Page-specific variables for header
$page_title = "Senior Science Projects (Grades 9-12) - Free Science Project";
$page_description = "Senior science projects for grades 9-12 (ages 14-18). Advanced research projects in biology, earth science, chemistry, and meteorology for high school students.";
$page_keywords = "senior science projects, grades 9-12, high school science, advanced biology, earth science, chemistry research, meteorology studies";
$canonical_url = "https://freescienceproject.com/senior_projects.php";
$content_group1 = "Senior Projects";
$content_group2 = "Grades 9-12";

// Breadcrumbs
$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Senior Projects (Grades 9-12)']
];

// Schema.org markup
$schema_markup = json_encode([
  "@context" => "https://schema.org",
  "@type" => "CollectionPage",
  "name" => "Senior Science Projects (Grades 9-12)",
  "description" => "Advanced research projects for senior students grades 9-12, ages 14-18. Biology, earth science, chemistry, and meteorology experiments.",
  "url" => "https://freescienceproject.com/senior_projects.php",
  "mainEntity" => [
    "@type" => "ItemList",
    "name" => "Senior Science Projects",
    "numberOfItems" => 15
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
      <h2>Senior Projects</h2>
      <p class="panel-subtitle">Grades 9-12 (ages 14-18)</p>
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
            <input type="checkbox" id="biology" name="subject" value="biology">
            <label for="biology">Biology</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="chemistry" name="subject" value="chemistry">
            <label for="chemistry">Chemistry</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="earth-science" name="subject" value="earth-science">
            <label for="earth-science">Earth Science</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="meteorology" name="subject" value="meteorology">
            <label for="meteorology">Meteorology</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="psychology" name="subject" value="psychology">
            <label for="psychology">Psychology</label>
          </div>
        </div>
      </div>

      <!-- Difficulty Filter -->
      <div class="filter-section">
        <h3>Difficulty</h3>
        <div class="filter-options">
          <div class="filter-option">
            <input type="checkbox" id="hard" name="difficulty" value="hard">
            <label for="hard">Hard</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="advanced" name="difficulty" value="advanced">
            <label for="advanced">Advanced</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="expert" name="difficulty" value="expert">
            <label for="expert">Expert</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="research" name="difficulty" value="research">
            <label for="research">Research Level</label>
          </div>
        </div>
      </div>

      <!-- Materials Filter -->
      <div class="filter-section">
        <h3>Materials</h3>
        <div class="filter-options">
          <div class="filter-option">
            <input type="checkbox" id="laboratory" name="materials" value="laboratory">
            <label for="laboratory">Lab Equipment</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="electrical" name="materials" value="electrical">
            <label for="electrical">Electrical Parts</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="craft" name="materials" value="craft">
            <label for="craft">Craft Supplies</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="specialized" name="materials" value="specialized">
            <label for="specialized">Specialized Equipment</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="research-tools" name="materials" value="research-tools">
            <label for="research-tools">Research Tools</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="field-work" name="materials" value="field-work">
            <label for="field-work">Field Work</label>
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
      <h4>Senior Research Safety!</h4>
      <p>These advanced projects require proper laboratory protocols, safety equipment, and often institutional supervision. Follow all safety guidelines, obtain necessary permits for research, and ensure proper waste disposal. Some projects may involve hazardous materials or require ethical review.</p>
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
        <button class="clear-filters" onclick="clearAllFilters()">Clear All Filters</button>
      </div>
    </div>

    <!-- Continue Learning Section -->
    <div class="content-container">
      <h2>Continue Your Science Journey</h2>
      <p>Ready for different challenges? Explore our other grade levels:</p>
      <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
        <a href="intermediate_projects.php" style="background: var(--accent-color, #34c759); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">← Intermediate Projects</a>
        <a href="index.php" style="background: var(--primary-color, #2c5aa0); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">← Back to Home</a>
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
    console.log('Senior Projects page loaded');
    console.log('searchUtils available:', typeof searchUtils !== 'undefined');
    console.log('CategoryPageManager available:', typeof CategoryPageManager !== 'undefined');
    
    // The CategoryPageManager will auto-initialize based on URL path
});
</script>
