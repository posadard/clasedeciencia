<?php
// Page-specific variables for header
$page_title = "All Science Projects - Complete Catalog - Free Science Project";
$page_description = "Complete catalog of all science projects for grades K-12. Browse hundreds of science experiments covering physics, chemistry, biology, earth science, and more.";
$page_keywords = "science projects catalog, all science experiments, K-12 science projects, physics experiments, chemistry projects, biology activities, earth science";
$canonical_url = "https://freescienceproject.com/projects/";
$content_group1 = "All Projects";
$content_group2 = "Complete Catalog";

// Breadcrumbs
$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'All Science Projects']
];

// Schema.org markup
$schema_markup = json_encode([
  "@context" => "https://schema.org",
  "@type" => "CollectionPage",
  "name" => "All Science Projects - Complete Catalog",
  "description" => "Complete catalog of all science projects for grades K-12. Hundreds of experiments covering all scientific disciplines.",
  "url" => "https://freescienceproject.com/projects/",
  "mainEntity" => [
    "@type" => "ItemList",
    "name" => "All Science Projects",
    "numberOfItems" => 200
  ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// No additional CSS needed - using main styles from header

// Include header
include_once __DIR__ . '/../includes/header.php';
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
      <h2>All Science Projects</h2>
      <p class="panel-subtitle">Complete Catalog (K-12)</p>
    </div>
    
    <!-- Panel Content -->
    <div class="panel-content">
      <!-- Search Box -->
      <div class="search-container">
        <form class="search-form" role="search">
          <input type="text" id="search-projects" class="search-box" placeholder="Search all projects...">
        </form>
      </div>
      
      <!-- Grade Level Filter -->
      <div class="filter-section">
        <h3>Grade Level</h3>
        <div class="filter-options">
          <div class="filter-option">
            <input type="checkbox" id="primary" name="category" value="primary">
            <label for="primary">Primary (K-4)</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="elementary" name="category" value="elementary">
            <label for="elementary">Elementary (4-6)</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="intermediate" name="category" value="intermediate">
            <label for="intermediate">Intermediate (7-8)</label>
          </div>
          <div class="filter-option">
            <input type="checkbox" id="senior" name="category" value="senior">
            <label for="senior">Senior (9-12)</label>
          </div>
        </div>
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
            <input type="checkbox" id="earth" name="subject" value="earth">
            <label for="earth">Earth Science</label>
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
            <input type="checkbox" id="biological" name="materials" value="biological">
            <label for="biological">Biological</label>
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
    <!-- Info Notice -->
    <section class="safety-notice">
      <h4>Complete Science Projects Catalog</h4>
      <p>Browse our complete collection of over 200 science projects for all grade levels. Use the filters on the left to find projects by grade level, subject, difficulty, or materials needed. Each project includes detailed instructions and educational explanations.</p>
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
            <option value="grade">Sort by Grade Level</option>
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

    <!-- Grade Level Navigation -->
    <div class="content-container">
      <h2>Browse by Grade Level</h2>
      <p>Looking for projects for a specific grade level? Check out our specialized collections:</p>
      <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
        <a href="/primary_projects.php" style="background: #4CAF50; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">Primary (K-4)</a>
        <a href="/elementary_projects.php" style="background: #2196F3; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">Elementary (4-6)</a>
        <a href="/intermediate_projects.php" style="background: #FF9800; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">Intermediate (7-8)</a>
        <a href="/senior_projects.php" style="background: #9C27B0; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">Senior (9-12)</a>
      </div>
    </div>
  </div>
</div>

</main>

<?php include_once __DIR__ . '/../includes/section-footer.php'; ?>

<!-- Enhanced Search Scripts -->
<script src="/js/science-projects-data.js?v=<?php echo filemtime(__DIR__ . '/../js/science-projects-data.js'); ?>"></script>
<script src="/js/category-page.js?v=<?php echo filemtime(__DIR__ . '/../js/category-page.js'); ?>"></script>

<script>
// Custom project loader for ALL projects (no category filter)
document.addEventListener('DOMContentLoaded', function() {
    console.log('All Projects catalog page loaded');
    
    // Create a modified CategoryPageManager that shows ALL projects
    if (typeof CategoryPageManager !== 'undefined' && typeof searchUtils !== 'undefined') {
        // Create custom instance that overrides loadProjects to get ALL projects
        const allProjectsManager = new CategoryPageManager('all');
        
        // Override the loadProjects method to get ALL projects
        allProjectsManager.loadProjects = function() {
            console.log('Loading ALL projects for complete catalog');
            if (typeof searchUtils !== 'undefined') {
                this.projects = searchUtils.getAllProjects();
                this.filteredProjects = [...this.projects];
                console.log(`Loaded ${this.projects.length} total projects`);
                console.log('First few projects:', this.projects.slice(0, 3));
            } else {
                console.error('searchUtils not available');
            }
        };
        
        // Initialize the manager
        allProjectsManager.init();
        
        // Store reference globally for URL search handling
        window.allProjectsManager = allProjectsManager;
    }
    
    // Handle search parameter from URL with better mobile support
    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get('search');
    if (searchTerm) {
        console.log('URL search parameter detected:', searchTerm);
        
        // Function to apply search term
        function applyUrlSearch() {
            const searchInput = document.getElementById('search-projects');
            if (searchInput && window.allProjectsManager && window.allProjectsManager.isReady && window.allProjectsManager.projects.length > 0) {
                console.log('Applying URL search term:', searchTerm);
                
                // Use the dedicated method for URL search
                window.allProjectsManager.applyUrlSearch(searchTerm);
                
                // Scroll to results if on mobile
                if (window.innerWidth <= 768) {
                    setTimeout(() => {
                        const resultsContainer = document.getElementById('projects-container');
                        if (resultsContainer) {
                            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }, 300);
                }
                
                console.log('URL search applied successfully');
                return true;
            }
            return false;
        }
        
        // Try multiple times with increasing delays for mobile compatibility
        let attempts = 0;
        const maxAttempts = 10;
        
        function attemptUrlSearch() {
            attempts++;
            console.log(`URL search attempt ${attempts}/${maxAttempts}`);
            
            if (applyUrlSearch()) {
                console.log('URL search successful on attempt', attempts);
                return;
            }
            
            if (attempts < maxAttempts) {
                const delay = Math.min(attempts * 200, 2000); // Progressive delay up to 2 seconds
                setTimeout(attemptUrlSearch, delay);
            } else {
                console.error('Failed to apply URL search after', maxAttempts, 'attempts');
            }
        }
        
        // Start first attempt with a longer initial delay for mobile
        setTimeout(attemptUrlSearch, 300);
        
        // Also listen for the ready event as a backup
        document.addEventListener('categoryManagerReady', function(e) {
            console.log('CategoryManager ready event received');
            if (!document.getElementById('search-projects').value) {
                setTimeout(() => applyUrlSearch(), 100);
            }
        });
    }
});
</script>
?>
