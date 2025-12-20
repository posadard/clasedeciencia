<?php
// Get project data and set up page variables
require_once __DIR__ . '/../includes/project-helpers.php';

$projectCode = isset($project_id) ? $project_id : 'PROJECT_CODE_PLACEHOLDER';
$project = getProjectData($projectCode);

if (!$project) {
    header('Location: /');
    exit;
}

// Set up page variables for header
$page_title = $project['title'] . " - " . ucfirst($project['category']) . " Science Project";
$page_description = $project['description'] . " Perfect for " . $project['grade'] . " students. Difficulty: " . $project['difficulty'] . ". Materials: " . $project['materials'] . ".";
$canonical_url = "https://freescienceproject.com/projects/" . $projectCode . ".php";
$page_keywords = $project['subject'] . " project, " . $project['category'] . " science, " . $project['grade'] . " experiment, " . $project['difficulty'] . " science project, " . $project['materials'] . " materials";

// Additional CSS for project landing pages
$additional_css = [
    '/css/project-landing.css?v=' . filemtime(__DIR__ . '/../css/project-landing.css')
];

// Schema.org structured data
$schema_data = json_encode([
    "@context" => "https://schema.org",
    "@type" => "Course",
    "name" => $project['title'],
    "description" => $project['description'],
    "about" => ucfirst($project['subject']) . " science experiment",
    "provider" => [
        "@type" => "Organization",
        "name" => "Free Science Project",
        "url" => "https://freescienceproject.com"
    ],
    "educationalLevel" => $project['grade'],
    "teaches" => $project['description'],
    "learningResourceType" => "hands-on experiment",
    "educationalUse" => "science fair project",
    "keywords" => $project['subject'] . " experiment, " . $project['difficulty'] . " difficulty, " . $project['grade'] . " grade, " . $project['materials'] . " materials, " . $project['category'] . " science",
    "url" => $canonical_url,
    "inLanguage" => "en-US",
    "isAccessibleForFree" => true
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

include __DIR__ . '/../includes/header.php';
?>

<script type="application/ld+json">
<?php echo $schema_data; ?>
</script>

<!-- Include FAQ categories JavaScript -->
<script src="/js/project-faq-categories.js"></script>

<main class="project-landing">
    <!-- Project Header -->
    <section class="project-header">
        <div class="container">
            <div class="project-category-badge">
                <?php echo ucfirst(htmlspecialchars($project['category'])); ?> Science Project
            </div>
            <h1><?php echo getSubjectIcon($project['subject']); ?> <?php echo htmlspecialchars($project['title']); ?></h1>
            <p class="project-subtitle"><?php echo htmlspecialchars($project['description']); ?></p>
            <div class="project-meta">
                <span class="meta-badge grade-badge">Grade: <?php echo htmlspecialchars($project['grade']); ?></span>
                <span class="meta-badge subject-badge">Subject: <?php echo ucfirst(htmlspecialchars($project['subject'])); ?></span>
                <span class="meta-badge difficulty-badge" style="background-color: <?php echo getDifficultyColor($project['difficulty']); ?>">
                    Difficulty: <?php echo ucfirst(htmlspecialchars($project['difficulty'])); ?>
                </span>
                <span class="meta-badge materials-badge">Materials: <?php echo ucfirst(htmlspecialchars($project['materials'])); ?></span>
            </div>
        </div>
    </section>

    <div class="container project-content">

     <!-- Call to Action -->
        <div class="project-cta">
            <h2>Ready to Start Your Experiment? <br><strong>"<?php echo htmlspecialchars($project['title']); ?>"</strong></h2>
            <p>Get detailed step-by-step instructions, materials list, and educational explanations for this exciting science project!</p>
            <a href="<?php echo htmlspecialchars($project['link_url']); ?>" class="cta-button">
                View Complete Project Instructions
            </a>
        </div>

        <!-- Project Overview -->
        <div class="project-overview">
            <div class="project-details">
                <h2>About This Project</h2>
                <p class="project-description-main"><?php echo htmlspecialchars($project['description']); ?></p>
                
                <p>This hands-on <?php echo htmlspecialchars($project['subject']); ?> experiment is specifically designed for <?php echo htmlspecialchars($project['grade']); ?> students and provides an excellent introduction to scientific concepts through practical learning.</p>
                
                <p>Students will develop critical thinking skills, learn to form hypotheses, and understand the scientific method through direct experimentation and observation.</p>
                
                <!-- Project Tags within About Section -->
                <div class="project-tags-inline">
                    <h4>Project Tags</h4>
                    <div class="tags-container">
                        <span class="tag tag-subject"><?php echo ucfirst(htmlspecialchars($project['subject'])); ?></span>
                        <span class="tag tag-category"><?php echo ucfirst(htmlspecialchars($project['category'])); ?></span>
                        <span class="tag tag-grade"><?php echo htmlspecialchars($project['grade']); ?></span>
                        <span class="tag tag-difficulty"><?php echo ucfirst(htmlspecialchars($project['difficulty'])); ?></span>
                        <span class="tag tag-materials"><?php echo ucfirst(htmlspecialchars($project['materials'])); ?> Materials</span>
                        <?php if ($project['difficulty'] === 'easy'): ?>
                        <span class="tag tag-beginner">Beginner Friendly</span>
                        <?php endif; ?>
                        <?php if (in_array($project['materials'], ['household', 'food', 'water'])): ?>
                        <span class="tag tag-household">Household Items</span>
                        <?php endif; ?>
                        <?php if ($project['category'] === 'primary'): ?>
                        <span class="tag tag-elementary">Elementary Level</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Project Supplies -->
            <div class="project-supplies-sidebar">
                <h3>Looking for Project Supplies?</h3>
                <p class="supplies-intro">Get everything you need for this experiment:</p>
                <div class="supplies-list">
                    <?php 
                    $shopCategories = getProjectShopCategories($project);
                    foreach ($shopCategories as $cat): 
                        // Extract the last part of the category name (after the last ">")
                        $categoryParts = explode(' > ', $cat['name']);
                        $displayName = end($categoryParts);
                    ?>
                        <a href="https://shop.miniscience.com/index.php?rt=product/category&path=<?php echo $cat['id']; ?>" 
                           class="supply-link" 
                           target="_blank" 
                           rel="noopener">
                            <?php echo htmlspecialchars($displayName); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>


        </div>

       <!-- Safety Notice -->
        <div class="safety-notice">
            <h3><span class="warning-icon">⚠</span> Safety First</h3>
            <p>Always have adult supervision when conducting experiments. Read all instructions carefully before starting, and ensure you have proper safety equipment as needed.</p>
        </div> 

       

        <!-- Dynamic FAQ Section -->
        <div id="dynamic-faq-container">
            <!-- FAQs will be generated by JavaScript based on project data -->
        </div>
    </div>
</main>

<script>
function toggleFAQ(button) {
    console.log('toggleFAQ called', button);
    const faqItem = button.parentElement;
    const answer = faqItem.querySelector('.faq-answer');
    
    console.log('faqItem:', faqItem);
    console.log('answer:', answer);
    
    // Toggle active state - let CSS handle the display
    faqItem.classList.toggle('active');
    
    console.log('active class toggled, current classes:', faqItem.className);
    
    // Update aria-expanded for accessibility
    const isActive = faqItem.classList.contains('active');
    button.setAttribute('aria-expanded', isActive ? 'true' : 'false');
    
    console.log('isActive:', isActive);
}

// Initialize FAQ state
document.addEventListener('DOMContentLoaded', function() {
    // Project data for dynamic FAQ generation
    const projectData = {
        subject: '<?php echo addslashes($project['subject']); ?>',
        difficulty: '<?php echo addslashes($project['difficulty']); ?>',
        category: '<?php echo addslashes($project['category']); ?>',
        title: '<?php echo addslashes($project['title']); ?>',
        description: '<?php echo addslashes($project['description']); ?>'
    };
    
    // Generate and insert dynamic FAQs with Schema.org data
    if (typeof initializeProjectFAQs === 'function') {
        const faqContainer = document.getElementById('dynamic-faq-container');
        if (faqContainer) {
            faqContainer.innerHTML = initializeProjectFAQs(projectData, projectData.category);
        }
    }
    
    // Initialize FAQ toggle functionality after FAQs are generated
    setTimeout(() => {
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            // Ensure initial state is correct
            const button = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            
            if (button && answer) {
                // Remove any inline styles that might interfere with CSS
                answer.style.removeProperty('display');
                
                // Ensure proper initial aria state
                button.setAttribute('aria-expanded', 'false');
                
                // Ensure item starts without active class
                item.classList.remove('active');
                
                // Add click event listener as backup to onclick
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleFAQ(this);
                });
            }
        });
        
        console.log('FAQ system initialized with', faqItems.length, 'items');
    }, 100);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
