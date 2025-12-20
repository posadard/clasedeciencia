
<?php
// Page-specific variables for header
$page_title = "Free Science Fair Projects - Educational STEM Experiments for K-12 Students";
$page_description = "Discover 220+ free science fair project ideas and step-by-step experiments for students grades K-12. From physics and chemistry to biology and engineering - complete with materials lists, safety guidelines, and educational kits.";
$page_keywords = "science fair projects, STEM education, science experiments for kids, physics projects, chemistry experiments, biology activities, elementary science, middle school projects, high school research, educational science kits";
$canonical_url = "https://freescienceproject.com/";
$content_group1 = "Homepage";
$content_group2 = "All Grades";

// No breadcrumbs needed for homepage
$breadcrumbs = [];

// Schema.org markup for homepage
$schema_markup = json_encode([
  "@context" => "https://schema.org",
  "@type" => "WebSite",
  "name" => "Free Science Project",
  "url" => "https://freescienceproject.com",
  "description" => "Comprehensive collection of free science fair project ideas, experiments, and educational resources for students grades K-12.",
  "publisher" => [
    "@type" => "Organization",
    "name" => "Free Science Project"
  ],
  "potentialAction" => [
    "@type" => "SearchAction",
    "target" => [
      "@type" => "EntryPoint",
      "urlTemplate" => "https://freescienceproject.com/search?q={search_term_string}"
    ],
    "query-input" => [
      "@type" => "PropertyValueSpecification",
      "valueRequired" => true,
      "valueName" => "search_term_string"
    ]
  ],
  "mainEntity" => [
    "@type" => "ItemList",
    "name" => "Science Fair Project Categories",
    "itemListElement" => [
      [
        "@type" => "ListItem",
        "position" => 1,
        "item" => [
          "@type" => "Course",
          "name" => "Primary Projects (Grades 1-4)",
          "url" => "https://freescienceproject.com/primary_projects.php"
        ]
      ],
      [
        "@type" => "ListItem",
        "position" => 2,
        "item" => [
          "@type" => "Course",
          "name" => "Elementary Projects (Grades 4-6)",
          "url" => "https://freescienceproject.com/elementary_projects.php"
        ]
      ],
      [
        "@type" => "ListItem",
        "position" => 3,
        "item" => [
          "@type" => "Course",
          "name" => "Intermediate Projects (Grades 7-8)",
          "url" => "https://freescienceproject.com/intermediate_projects.php"
        ]
      ],
      [
        "@type" => "ListItem",
        "position" => 4,
        "item" => [
          "@type" => "Course",
          "name" => "Senior Projects (Grades 9-12)",
          "url" => "https://freescienceproject.com/senior_projects.php"
        ]
      ]
    ]
  ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Include header
include_once __DIR__ . '/includes/header.php';
?>

  <!-- Hero Section -->
  <section class="hero-section" role="banner">
    <div class="container">
      <div class="hero-content">
        <h1 class="hero-title">Free Science Fair Projects</h1>
        <p class="hero-subtitle">Discover amazing STEM experiments and educational resources for students grades K-12</p>
        <div class="hero-buttons">
          <a href="/projects/" class="btn btn-primary">Explore Projects</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Main content -->
  <main id="main-content" role="main">
    
    <!-- Search Section -->
    <section class="search-container">
      <div class="container">
        <form class="search-form" role="search">
          <div class="search-box">
            <input type="search" class="search-input" placeholder="Search for science projects..." aria-label="Search science projects">
          </div>
        </form>
        <div class="filter-tabs">
          <a href="/primary_projects.php" class="filter-tab">Primary (K-4)</a>
          <a href="/elementary_projects.php" class="filter-tab">Elementary (4-6)</a>
          <a href="/intermediate_projects.php" class="filter-tab">Intermediate (7-8)</a>
          <a href="/senior_projects.php" class="filter-tab">Senior (9-12)</a>
        </div>
      </div>
    </section>

    <!-- Grade Levels Section -->
    <section class="grade-levels section">
      <div class="container">
        <h2 class="section-title">Choose Your Grade Level</h2>
        <div class="projects-grid">
          <article class="grade-level-card">
            <div class="grade-icon primary"></div>
            <h3>Primary Projects</h3>
            <p>Perfect for grades K-4 (ages 6-10). Simple, safe experiments that introduce basic scientific concepts through hands-on learning.</p>
            <div class="project-meta">
              
              <a href="/primary_projects.php" class="btn btn-primary">View Projects</a>
            </div>
          </article>
          
          <article class="grade-level-card">
            <div class="grade-icon elementary"></div>
            <h3>Elementary Projects</h3>
            <p>Designed for grades 4-6 (ages 10-14). More detailed experiments that build on fundamental science principles.</p>
            <div class="project-meta">
              
              <a href="/elementary_projects.php" class="btn btn-primary">View Projects</a>
            </div>
          </article>
          
          <article class="grade-level-card">
            <div class="grade-icon intermediate"></div>
            <h3>Intermediate Projects</h3>
            <p>Perfect for grades 7-8 (ages 13-15). Advanced experiments introducing complex scientific methodology.</p>
            <div class="project-meta">
              
              <a href="/intermediate_projects.php" class="btn btn-primary">View Projects</a>
            </div>
          </article>
          
          <article class="grade-level-card">
            <div class="grade-icon senior"></div>
            <h3>Senior Projects</h3>
            <p>Challenging projects for grades 9-12 (ages 14-18). Original research opportunities and advanced scientific investigation.</p>
            <div class="project-meta">
              
              <a href="/senior_projects.php" class="btn btn-primary">View Projects</a>
            </div>
          </article>
        </div>
      </div>
    </section>


		    
        

    <!-- Related Resources Section -->
    <section class="section" style="background: white;">
      <div class="container">
        <h2 class="section-title">Educational Resources & Science Kits</h2>
        <div class="projects-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
          <div class="project-card">
            <h3>Science Fair Project Kits</h3>
            <p>Complete experiment kits with all materials included. Perfect for hands-on learning and science fair competitions.</p>
            <a href="https://www.scienceproject.com" class="btn btn-secondary">Browse Kits</a>
          </div>
          
          <div class="project-card">
            <h3>School Science Supplies</h3>
            <p>Educational supplies for chemistry, physics, biology and electronics training. Bulk pricing for schools available.</p>
            <a href="http://SchoolOrders.com" class="btn btn-secondary">School Orders</a>
          </div>
          
          <div class="project-card">
            <h3>Student Science Supplies</h3>
            <p>Individual science supplies and educational materials. Fast shipping and competitive prices for students.</p>
            <a href="http://shop.MiniScience.com" class="btn btn-secondary">Student Supplies</a>
          </div>
          
          <div class="project-card">
            <h3>Additional Resources</h3>
            <p>Periodic table reference, science fair tips, and additional educational content to support your learning.</p>
            <a href="http://pt.chemicalstore.com" class="btn btn-secondary">More Resources</a>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section for GEO Optimization -->
    <section class="section" style="background: var(--background-light);">
      <div class="container">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <div class="faq-section" itemscope itemtype="https://schema.org/FAQPage">
          <article class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <h3 class="faq-question" itemprop="name">Are these science projects safe for children?</h3>
            <div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
              <div itemprop="text">Yes, all our science projects are designed with safety as the top priority. We provide clear safety guidelines for each experiment and recommend adult supervision for younger students. Projects are categorized by appropriate age groups to ensure age-appropriate activities.</div>
            </div>
          </article>

          <article class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <h3 class="faq-question" itemprop="name">How do I choose the right project for my grade level?</h3>
            <div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
              <div itemprop="text">Our projects are organized by grade level: Primary (K-4), Elementary (4-6), Intermediate (7-8), and Senior (9-12). Each category is designed to match the cognitive abilities and safety requirements of that age group. You can also browse by subject area to find projects that match your interests.</div>
            </div>
          </article>

          <article class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <h3 class="faq-question" itemprop="name">What materials do I need for these experiments?</h3>
            <div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
              <div itemprop="text">Each project includes a complete materials list. Most experiments use common household items or inexpensive supplies available at local stores. For convenience, we also offer complete science kits with all materials included and pre-measured for immediate use.</div>
            </div>
          </article>

          <article class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <h3 class="faq-question" itemprop="name">Can these projects be used for science fair competitions?</h3>
            <div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
              <div itemprop="text">Absolutely! Our projects are specifically designed for science fair competitions and classroom assignments. Each project follows the scientific method and includes hypothesis formation, experimentation, data collection, and conclusion drawing. Many students have won awards using our project ideas.</div>
            </div>
          </article>

          <article class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <h3 class="faq-question" itemprop="name">How long do these science projects take to complete?</h3>
            <div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
              <div itemprop="text">Project duration varies by complexity. Simple projects can be completed in 1-2 hours, while more advanced experiments may require several days or weeks for observation and data collection. Each project description includes estimated time requirements to help with planning.</div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- Why Choose Our Projects Section -->
    <section class="educational-value-section section">
      <div class="container">
        <div class="educational-value-content">
          <h2>Why Choose Our Science Projects?</h2>
          <p>A group of project advisors identified the best science projects for educational value, attractiveness, versatility, and availability of materials. Most projects focus on energy, environment, and health - key topics in modern STEM education.</p>
          <div class="value-highlights">
            <div class="value-item">
              <h3>Educational Excellence</h3>
              <p>Each project is carefully reviewed for its learning potential and alignment with STEM curriculum standards.</p>
            </div>
            <div class="value-item">
              <h3>Safety First</h3>
              <p>All experiments prioritize student safety with clear guidelines and age-appropriate materials.</p>
            </div>
            <div class="value-item">
              <h3>Real-World Relevance</h3>
              <p>Projects focus on current topics like energy conservation, environmental science, and health awareness.</p>
            </div>
          </div>
          <div class="section-cta" style="text-align: center; margin-top: 2.5rem;">
            <a href="https://www.scienceproject.com/" class="btn-primary" target="_blank" rel="noopener">Explore More Science Projects</a>
          </div>
        </div>
      </div>
    </section>

  </main>

<!-- Enhanced Search Scripts -->
<script src="/js/science-projects-data.js?v=<?php echo filemtime(__DIR__ . '/js/science-projects-data.js'); ?>"></script>
<script src="/js/home-search.js?v=<?php echo filemtime(__DIR__ . '/js/home-search.js'); ?>"></script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>