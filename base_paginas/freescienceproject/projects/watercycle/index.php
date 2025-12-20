<?php
// Project: Water Cycle Demonstration - Earth Science Experiment
$project_title = "Creating Your Own Water Cycle - Environmental Science Experiment";
$project_description = "Build a working model of the water cycle and observe evaporation, condensation, and precipitation in action. Learn about environmental science and the water cycle that sustains all life on Earth.";
$project_keywords = "water cycle, evaporation, condensation, precipitation, environmental science, earth science, weather, climate";
$project_grade_level = "Elementary"; // ages 6-12
$project_subject = "Earth Science, Environmental Science";
$project_difficulty = "Beginner";

// Project pages live under /projects/<slug>/ so include the shared project header
// using a path relative to this file that reaches the repository includes folder.
include __DIR__ . '/../../includes/project-header.php';
?>

<article class="project-article">

<section class="project-body container">
  <!-- Print buttons (top-right and bottom) -->
  <div class="print-controls" aria-hidden="false">
    <button class="btn-print" id="print-top" aria-label="Print this page">Print</button>
  </div>

  <!-- Print-only logo shown only in printed output -->
  <img class="print-logo" src="https://freescienceproject.com/images/freescienceproject02.jpg" alt="Free Science Project logo" />

  <h2 class="project-title-simple"><?php echo htmlspecialchars($project_title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>

  <style scoped>
    /* Scoped modern styles for this legacy project page - non-destructive */
    .project-body { max-width: 980px; margin: 0 auto; padding: 1rem; }
    .project-title-simple { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #2c5aa0; font-size: 1.5rem; margin: 0 0 0.75rem 0; }
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .problem-section { background: #fff6f0; border: 1px solid #ff6b35; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .research-section { background: #f0fff0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .materials-section { background: #f0f8ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .procedure-section { background: #fff8f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .results-section { background: #f8fff8; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .safety-section { background: #ffe6e6; border: 1px solid #ff4444; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .extensions-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .water-cycle-diagram { background: #e6f7ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #2c5aa0; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #2c5aa0; font-weight: bold; }
    .center { text-align: center; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .cycle-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1rem 0; }
    .step-card { background: white; border: 1px solid #ddd; padding: 1rem; border-radius: 8px; text-align: center; }
    /* Ensure all paragraphs have dark text for readability */
    p { color: #333; line-height: 1.6; }
    /* Footer styles - ensure all text in footer is white */
    footer, footer *, 
    .footer, .footer *,
    .project-footer, .project-footer *,
    [class*="footer"] *, [id*="footer"] * {
      color: white !important;
      fill: white !important;
    }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
    @media (max-width: 720px) {
      .banner-row { flex-direction: column; gap: 0.5rem; }
      .two-col { grid-template-columns: 1fr; }
    }
    /* Miniscience widget centering */
    .miniscience-widget { display:flex; justify-content:center; align-items:center; }
    .miniscience-widget .abantecart-widget-container { display:block; max-width:880px; width:100%; }
    .miniscience-widget .abantecart_product { margin:0 auto; }
    /* Related & FAQ per-project sections */
    .related-projects { margin-top:1.25rem; border-top:1px solid #e6e6e6; padding-top:1rem; }
    .related-projects h3 { margin-top:0; }
    .related-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; align-items:start; }
    .related-grid .miniscience-widget { margin: 0; }
    .faq { margin-top:1rem; }
    .faq details { margin-bottom:0.75rem; }
    /* Print controls */
    .print-controls { display:flex; justify-content:flex-end; margin-bottom:0.5rem; }
    .btn-print { background:#2c5aa0; color:#fff; border:0; padding:0.45rem 0.75rem; border-radius:6px; cursor:pointer; font-family:inherit; }
    .btn-print:focus { outline:3px solid #ffec99; }
    /* Print-only logo: hidden on screen */
    .print-logo { display: none; max-width:220px; }
    @media print {
      /* Hide everything by default when printing */
      body * { visibility: hidden; }
      /* Only show the print-clone, not the original project-body */
      .print-clone, .print-clone * { visibility: visible !important; }
      .print-clone { position: absolute; left: 0; top: 0; width: 100%; }
      /* Explicitly hide the original project-body to prevent duplication */
      .project-body:not(.print-clone) { display: none !important; }
      /* Hide print buttons in printed output */
      .btn-print { display: none !important; }
      /* Show print-only logo */
      .print-logo { display: block !important; max-width:220px; margin: 0 0 1rem 0; }
      /* Show print-only products info */
      .print-only-products { display: block !important; }
    }
  </style>

  <div class="intro-section">
    <p>The <strong class="highlight-text">Water Cycle Demonstration</strong> is a fundamental earth science experiment that shows how water moves through the environment. This hands-on activity teaches students about evaporation, condensation, and precipitation while demonstrating the continuous cycle that sustains all life on Earth.</p>
  </div>

  <div class="center">
    <img src="image001.gif" alt="Water cycle experiment demonstration" class="responsive-img" style="max-width: 400px; margin: 1rem auto;" />
  </div>

  <div class="problem-section">
    <h3 class="section-title">🔬 Problem Statement</h3>
    <p><strong>Research Question:</strong> What is the water cycle and how does it work?</p>
    <p>The water cycle is one of Earth's most important processes, but how can we observe and understand it? By creating a small-scale model, we can see how water continuously moves through evaporation, condensation, and precipitation.</p>
  </div>

  <div class="research-section">
    <h3 class="section-title">📚 Background Research</h3>
    
    <p><strong class="highlight-text">The Water Cycle:</strong> Every human, plant, and animal depends on water for survival. The water cycle is controlled by the sun, which produces energy in the form of heat.</p>
    
    <div class="water-cycle-diagram">
      <h4>The Four Main Stages of the Water Cycle:</h4>
      <div class="cycle-steps">
        <div class="step-card">
          <h5><strong>1. Evaporation</strong></h5>
          <p>Heat energy causes water in oceans, lakes, and puddles to warm and change from liquid to gas (water vapor).</p>
        </div>
        <div class="step-card">
          <h5><strong>2. Transpiration</strong></h5>
          <p>Plants release water vapor through their leaves as part of their natural processes.</p>
        </div>
        <div class="step-card">
          <h5><strong>3. Condensation</strong></h5>
          <p>Water vapor rises into cooler air, cools down, and changes back into liquid water droplets that form clouds.</p>
        </div>
        <div class="step-card">
          <h5><strong>4. Precipitation</strong></h5>
          <p>Water droplets in clouds become too heavy and fall as rain, snow, sleet, or hail.</p>
        </div>
      </div>
    </div>
    
    <p><strong>Why the Water Cycle Matters:</strong></p>
    <ul>
      <li><strong>Freshwater Supply:</strong> Provides clean water for drinking, agriculture, and ecosystems</li>
      <li><strong>Weather Patterns:</strong> Drives weather systems and climate around the world</li>
      <li><strong>Ecosystem Balance:</strong> Maintains moisture levels necessary for plant and animal life</li>
      <li><strong>Temperature Regulation:</strong> Helps moderate Earth's temperature through heat transfer</li>
    </ul>
  </div>

  <div class="problem-section">
    <h3 class="section-title">🧪 Hypothesis</h3>
    <p><strong>Our Prediction:</strong> We think that the water cycle is the way Earth uses and recycles water continuously, and we can demonstrate this process in a small-scale model using heat, evaporation, and condensation.</p>
  </div>

  <div class="materials-section">
    <h3 class="section-title">🛠️ Materials Needed</h3>
    
    <div class="two-col">
      <div>
        <p><strong>Container Setup:</strong></p>
        <ul>
          <li><strong>Large, clear bowl</strong> (glass or plastic)</li>
          <li><strong>Small container</strong> (cut-down yogurt cup or small bowl)</li>
          <li><strong>Plastic wrap</strong> (clear food wrap)</li>
          <li><strong>Rubber band or string</strong> (to secure plastic wrap)</li>
        </ul>
      </div>
      <div>
        <p><strong>Additional Materials:</strong></p>
        <ul>
          <li><strong>Small weight</strong> (coin, small stone, or marble)</li>
          <li><strong>Water</strong> (room temperature)</li>
          <li><strong>Sunny location</strong> (windowsill or outdoor spot)</li>
          <li><strong>Measuring cup</strong> (optional, for precise measurements)</li>
        </ul>
      </div>
    </div>
    
    <p><strong>Safety Note:</strong> This experiment uses only safe, common household materials and is appropriate for all ages with basic supervision.</p>
  </div>

  <div class="procedure-section">
    <h3 class="section-title">📋 Step-by-Step Procedure</h3>
    
    <ol>
      <li><strong>Setup the Collection System:</strong> Place the small container in the center of the large, clear bowl</li>
      <li><strong>Add Water:</strong> Fill the large bowl with a small amount of water (about 1 inch deep), being careful not to get water in the small container</li>
      <li><strong>Cover the System:</strong> Cover the entire bowl with plastic wrap, ensuring it's completely sealed</li>
      <li><strong>Secure the Cover:</strong> Use a rubber band or string to fasten the plastic wrap tightly around the rim of the bowl</li>
      <li><strong>Create a Collection Point:</strong> Place a small weight on top of the plastic wrap directly above the small container (this creates a low point for water to drip)</li>
      <li><strong>Position for Heat:</strong> Place your water cycle model in a sunny location such as a windowsill where sunlight will hit it</li>
      <li><strong>Observe and Record:</strong> Watch for changes over the next few hours and record your observations</li>
    </ol>
    
    <h4>Observation Questions:</h4>
    <ul>
      <li>How long does it take for water to evaporate and condense on the plastic wrap?</li>
      <li>Where does the water go after it condenses on the plastic wrap?</li>
      <li>How much water collects in the small container after different time periods?</li>
    </ul>
  </div>

  <div class="center">
    <img src="image008.jpg" alt="Complete water cycle demonstration setup" class="responsive-img" style="max-width: 400px; margin: 1rem auto;" />
  </div>

  <div class="results-section">
    <h3 class="section-title">📊 Record and Analyze Data</h3>
    
    <p><strong class="highlight-text">What Happens:</strong> The heat of the sun evaporates the water from the large bowl. This water vapor rises, hits the cool plastic wrap, condenses back into liquid water, and drips down into the small container.</p>
    
    <p><strong>Results Explanation:</strong></p>
    <ul>
      <li><strong>Evaporation:</strong> Solar heat energy converts liquid water to water vapor</li>
      <li><strong>Condensation:</strong> Water vapor cools on the plastic wrap and returns to liquid form</li>
      <li><strong>Collection:</strong> Condensed water drips into the collection container, representing precipitation</li>
      <li><strong>Cycle Completion:</strong> This demonstrates a complete, small-scale water cycle</li>
    </ul>
    
    <p><strong>Real-World Connection:</strong> This is a small-scale replica of the water cycle that occurs every day on Earth. Your model shows the same processes that create rain, fill rivers, and provide fresh water for all living things.</p>
  </div>

  <div class="extensions-section">
    <h3 class="section-title">🚀 Experiment Extensions & Variations</h3>
    
    <h4>Advanced Investigations:</h4>
    <ul>
      <li><strong>Temperature Testing:</strong> Try the experiment in different temperatures (warm vs. cool locations)</li>
      <li><strong>Light Source Comparison:</strong> Compare results using sunlight vs. artificial light sources</li>
      <li><strong>Container Size Effects:</strong> Test how different sized containers affect evaporation rates</li>
      <li><strong>Time Studies:</strong> Measure water collection at different time intervals (1 hour, 4 hours, 24 hours)</li>
    </ul>
    
    <h4>Real-World Applications:</h4>
    <ul>
      <li><strong>Weather Prediction:</strong> Study how understanding the water cycle helps meteorologists predict weather</li>
      <li><strong>Climate Change:</strong> Research how global warming affects the water cycle</li>
      <li><strong>Water Conservation:</strong> Explore how the water cycle relates to water conservation efforts</li>
      <li><strong>Agricultural Impact:</strong> Investigate how farmers rely on the water cycle for crop irrigation</li>
    </ul>
    
    <h4>Additional Experiments:</h4>
    <ul>
      <li><strong>Salt Water Testing:</strong> Try with salt water to see if salt transfers during evaporation</li>
      <li><strong>Colored Water:</strong> Use food coloring to track water movement more easily</li>
      <li><strong>Multiple Containers:</strong> Set up several models to test different variables simultaneously</li>
    </ul>
  </div>

  <div class="safety-section">
    <h3 class="section-title">⚠️ Safety Considerations</h3>
    <ul>
      <li><strong>Sun Safety:</strong> If placing outside, ensure the location is safe and supervised</li>
      <li><strong>Glass Safety:</strong> If using glass bowls, handle carefully to prevent breaking</li>
      <li><strong>Water Safety:</strong> Clean up any spills immediately to prevent slipping</li>
      <li><strong>Hygiene:</strong> Wash hands after handling materials and use clean containers</li>
      <li><strong>Adult Supervision:</strong> Recommended for younger children, especially when handling glass</li>
    </ul>
  </div>

  <div class="banner-row" role="group" aria-label="Page banners">
    <div class="banner">
      <a href="http://www.ScienceProject.com"><img src="../../images/spbanner1.jpg" alt="Join ScienceProject.com - support with your science project" width="200" height="81"></a>
    </div>
    <div class="banner">
      <a href="../../projects"><img src="../../images/btr_index.jpg" alt="Index of projects" width="98" height="32"></a>
      <a href="/"><img src="../../images/btr_home.jpg" alt="Back to home" width="98" height="30" style="margin-left:0.5rem;"></a>
    </div>
    <div class="banner">
      <a href="http://store.MiniScience.com"><img src="../../images/msbanner1.jpg" alt="MiniScience store banner" width="200" height="81"></a>
    </div>
  </div>

  <!-- Print-only related products info -->
  <div class="print-only-products" style="display: none; margin-top: 1.5rem; padding: 1rem; border-top: 2px solid #2c5aa0;">
    <p style="margin: 0; font-weight: bold; color: #2c5aa0;">Where can I find related products? miniscience.com</p>
  </div>

</section>

<!-- MiniScience / Miniscience product and category embed widget -->
<section class="related-projects">
  <h3>Related Science Projects &amp; Kits</h3>
  
  <!-- Kit/Products Section -->
  <div class="related-grid" style="margin-bottom: 1.5rem;">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_648" class="abantecart_product" data-product-id="3399" data-language="en" data-currency="USD">
          <div class="abantecart_image"></div>
          <h3 class="abantecart_name"></h3>
          <div class="abantecart_blurb"></div>
          <div class="abantecart_rating"></div>
          <div class="abantecart_addtocart"></div>
        </div>
      </div>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_102" class="abantecart_product" data-product-id="7265" data-language="en" data-currency="USD">
          <div class="abantecart_image"></div>
          <h3 class="abantecart_name"></h3>
          <div class="abantecart_blurb"></div>
          <div class="abantecart_rating"></div>
          <div class="abantecart_addtocart"></div>
        </div>
      </div>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_718" class="abantecart_product" data-product-id="2396" data-language="en" data-currency="USD">
          <div class="abantecart_image"></div>
          <h3 class="abantecart_name"></h3>
          <div class="abantecart_blurb"></div>
          <div class="abantecart_rating"></div>
          <div class="abantecart_addtocart"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Related Categories Section -->
  <h4 style="margin-top: 1rem; margin-bottom: 0.75rem; color: #2c5aa0; font-size: 1.1rem;">Related Categories</h4>
  <div class="related-grid">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760464461106106" class="abantecart_category" data-category-id="106" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760464483492139" class="abantecart_category" data-category-id="139" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>
  </div>
</section>

<section class="faq">
  <h3>Frequently Asked Questions</h3>
  <details>
    <summary>How long does it take to see results in the water cycle experiment?</summary>
    <p>You should start seeing water droplets forming on the plastic wrap within 30-60 minutes in bright sunlight. Significant water collection in the small container typically occurs after 2-4 hours, depending on temperature and light conditions.</p>
  </details>
  <details>
    <summary>Why doesn't my water cycle model work as fast as expected?</summary>
    <p>Several factors affect the speed: sunlight intensity, air temperature, humidity levels, and the seal quality of your plastic wrap. Try moving to a sunnier location, ensuring a tight seal, or waiting longer for better results.</p>
  </details>
  <details>
    <summary>Can I use this experiment to purify water?</summary>
    <p>Yes! This demonstrates water distillation. If you start with salt water or dirty water, the evaporated and condensed water in the collection container will be pure. This is the same principle used in solar stills and desalination.</p>
  </details>
  <details>
    <summary>What happens if I use hot water instead of room temperature water?</summary>
    <p>Hot water will accelerate the evaporation process, producing faster results. However, be careful with hot water around children and ensure the container can handle the temperature without cracking.</p>
  </details>
  <details>
    <summary>Why is the weight important on the plastic wrap?</summary>
    <p>The weight creates a low point that guides condensed water droplets to fall into the collection container rather than running down the sides of the bowl. Without it, much of the condensed water would be lost.</p>
  </details>
  <details>
    <summary>How does this relate to real weather patterns?</summary>
    <p>Your model demonstrates the same processes that create weather: solar heating causes evaporation from oceans and lakes, water vapor rises and cools to form clouds, and condensed water falls as precipitation (rain, snow, etc.).</p>
  </details>
  <details>
    <summary>Can I do this experiment without sunlight?</summary>
    <p>Yes, but it will be much slower. You can use a warm lamp (like a desk lamp) positioned above the setup, or place it near a heat source. The key is providing enough heat energy to drive evaporation.</p>
  </details>
  <details>
    <summary>What should I do with the collected water?</summary>
    <p>The collected water is pure distilled water (assuming you started with clean water). You can measure it, taste it (if started with clean water), or use it for other experiments. It's perfect for demonstrating the purification aspect of the water cycle.</p>
  </details>
</section>

<!-- Bottom print control -->
<div class="print-controls" style="margin:1rem 0;">
  <button class="btn-print" id="print-bottom" aria-label="Print this page">Print</button>
</div>

<script>
// Print only the project-body section by opening a print window with cloned content.
function printProjectBody() {
  try {
    const section = document.querySelector('.project-body');
    if (!section) return window.print();

    // Create a hidden print container and clone the section into it
    const printContainer = document.createElement('div');
    // Give the clone the project-body class so @media print rules make it visible
    printContainer.className = 'project-body print-clone';
    // Prevent the clone from affecting layout before printing
    printContainer.style.display = 'none';
    // Copy content
    printContainer.innerHTML = section.innerHTML;
    // Small inline print-friendly adjustments to reduce blank pages
    printContainer.style.boxSizing = 'border-box';
    printContainer.style.width = '100%';
    printContainer.style.maxWidth = '980px';
    printContainer.style.margin = '0 auto';
    document.body.appendChild(printContainer);

    // Temporarily make the clone visible for printing via CSS media rules
    printContainer.style.display = 'block';
    // Ensure the clone is visible to the print stylesheet
    printContainer.style.visibility = 'visible';

    // Trigger print
    window.print();

    // Clean up after a short delay to allow print dialog to start
    setTimeout(() => {
      try { document.body.removeChild(printContainer); } catch (e) { /* ignore */ }
    }, 500);
  } catch (e) {
    console && console.error && console.error('Print failed', e);
    window.print();
  }
}

document.getElementById('print-top')?.addEventListener('click', printProjectBody);
document.getElementById('print-bottom')?.addEventListener('click', printProjectBody);
</script>

<?php
include __DIR__ . '/../../includes/project-footer.php';
?>