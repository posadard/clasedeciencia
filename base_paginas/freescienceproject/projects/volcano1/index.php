<?php
// Project: Volcano Eruption - DIY Educational Project
$project_title = "Build and Erupt Your Own Volcano - DIY Science Project";
$project_description = "Create an exciting volcano model and learn about volcanic eruptions, geology, and earth science through hands-on experimentation using common household materials.";
$project_keywords = "volcano model, volcanic eruption, geology project, earth science, DIY volcano, baking soda volcano, science fair project";
$project_grade_level = "Elementary to Middle"; // all ages with supervision
$project_subject = "Earth Science, Geology";
$project_difficulty = "Beginner to Intermediate";

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
    .project-title-simple { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #990000; font-size: 1.6rem; margin: 0 0 0.75rem 0; text-align: center; font-weight: bold; }
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .info-box { border: 1px solid #69c; background: #f4f8ff; padding: 0.75rem; color: #0033cc; font-family: Verdana, Geneva, Tahoma, sans-serif; margin: 1rem 0; }
    .geological-box { border: 1px solid #ff6b35; background: #fff8f0; padding: 1rem; margin: 1rem 0; }
    .safety-box { border: 2px solid #ff0000; background: #ffefef; padding: 1rem; margin: 1rem 0; }
    .materials-box { border: 1px solid #34c759; background: #f0fff0; padding: 1rem; margin: 1rem 0; }
    .diy-notice { background: #e8f5e8; border: 2px solid #34c759; padding: 1rem; border-radius: 8px; margin: 1rem 0; text-align: center; }
    .section-title { color: #990000; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #990000; font-weight: bold; }
    .red-text { color: #ff0000; font-weight: bold; }
    .materials-grid { display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start; margin: 1rem 0; }
    .two-col { display: grid; grid-template-columns: 1fr 320px; gap: 1rem; align-items: start; margin: 1rem 0; }
    .three-col { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; border-radius: 8px; }
    .center { text-align: center; }
    .step-box { background: #f9f9f9; border-left: 4px solid #ff6b35; padding: 1rem; margin: 0.5rem 0; }
    .volcano-types { background: #fff5f0; border: 1px solid #ff6b35; padding: 1rem; margin: 1rem 0; }
    .experiment-variations { background: #f0f8ff; border: 1px solid #2c5aa0; padding: 1rem; margin: 1rem 0; }
    @media (max-width: 720px) {
      .materials-grid, .two-col, .three-col { grid-template-columns: 1fr; }
      .banner-row { grid-template-columns: 1fr; }
    }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
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
    /* Footer styles - ensure all text in footer is white */
    footer, footer *, 
    .footer, .footer *,
    .project-footer, .project-footer *,
    [class*="footer"] *, [id*="footer"] * {
      color: white !important;
      fill: white !important;
    }
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

  <div class="diy-notice">
    <p><strong>DIY Project Notice:</strong> This volcano project is easy and safe to make at home using common household materials! No special kit required - everything you need can be found in your kitchen and craft supplies.</p>
  </div>

  <div class="intro-section">
    <p><strong class="highlight-text">Simulate the Awesome Power of an Erupting Volcano!</strong> Create an exciting volcano model and learn about these incredible geological phenomena. This easy, safe, and fun project lets you recreate the spectacular sight of a volcanic eruption right in your home or classroom!</p>
  </div>

  <div class="materials-grid">
    <div>
      <img src="volcano.jpg" alt="DIY volcano model erupting with safe household materials" class="responsive-img" />
      <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;"><strong>Safe for all ages with adult supervision</strong></p>
    </div>
    <div>
      <h3 class="highlight-text">Easy DIY Volcano Construction</h3>
      
      <p>Build your own volcano using simple materials you probably already have at home. This project combines <strong>chemistry, geology, and engineering</strong> into one exciting educational experience.</p>
      
      <p><strong>Perfect for:</strong> Science fair projects, classroom demonstrations, family science activities, or just curious kids who love explosive reactions!</p>
      
      <p class="red-text">Adult supervision recommended when handling materials and during eruption.</p>
    </div>
  </div>

  <h3 class="section-title">Materials You'll Need (Common Household Items):</h3>
  
  <div class="materials-box">
    <h4><strong>For the Volcano Structure:</strong></h4>
    <div class="three-col">
      <div>
        <strong>Base & Shape:</strong>
        <ul>
          <li>Large cardboard or wooden base</li>
          <li>Empty plastic bottle (16-20 oz)</li>
          <li>Modeling clay or paper mache</li>
          <li>Aluminum foil (optional)</li>
        </ul>
      </div>
      <div>
        <strong>Decoration:</strong>
        <ul>
          <li>Brown, gray, and black paint</li>
          <li>Small rocks or gravel</li>
          <li>Sand (optional)</li>
          <li>Paintbrushes</li>
        </ul>
      </div>
      <div>
        <strong>Eruption Materials:</strong>
        <ul>
          <li>Baking soda (sodium bicarbonate)</li>
          <li>White vinegar</li>
          <li>Liquid dish soap</li>
          <li>Red and yellow food coloring</li>
        </ul>
      </div>
    </div>
  </div>

  <h3 class="section-title">Step-by-Step Construction Guide:</h3>
  
  <div class="step-box">
    <h4><strong>Step 1: Create the Base Structure</strong></h4>
    <p>Place the empty plastic bottle in the center of your base. This will be your volcano's "magma chamber" where the eruption happens. Secure it with clay or tape so it won't move during construction.</p>
  </div>

  <div class="step-box">
    <h4><strong>Step 2: Build the Volcano Shape</strong></h4>
    <p>Use modeling clay, paper mache, or crumpled newspaper and tape to build up the volcanic cone around the bottle. Leave the bottle opening exposed at the top. Make the slopes realistic - not too steep or too gradual.</p>
  </div>

  <div class="step-box">
    <h4><strong>Step 3: Add Realistic Details</strong></h4>
    <p>Paint your volcano with browns, grays, and blacks to look like real volcanic rock. Add texture with small stones or sand while paint is wet. Create rocky outcroppings and lava flows from previous "eruptions."</p>
  </div>

  <div class="step-box">
    <h4><strong>Step 4: Prepare for Eruption</strong></h4>
    <p>Mix your eruption ingredients carefully. The basic formula: 2 tablespoons baking soda, 1/4 cup vinegar, red food coloring, and a squirt of dish soap for foamy "lava."</p>
  </div>

  <div class="center">
    <img src="volcano_box.jpg" alt="Example of completed DIY volcano model ready for eruption" class="responsive-img" style="max-width: 400px; margin: 1rem auto;" />
  </div>

  <h3 class="section-title">The Science Behind Volcanic Eruptions:</h3>
  
  <div class="geological-box">
    <h4 class="highlight-text">Real Volcano Science:</h4>
    <p><strong>Pressure Buildup:</strong> Real volcanoes erupt when molten rock (magma) builds up pressure beneath Earth's surface. Gas bubbles in the magma expand, creating explosive force.</p>
    
    <p><strong>Chemical Reaction:</strong> Our model uses an acid-base reaction between vinegar (acetic acid) and baking soda (sodium bicarbonate) to produce carbon dioxide gas, simulating the gas expansion in real eruptions.</p>
    
    <p><strong>Eruption Types:</strong></p>
    <ul>
      <li><strong>Explosive:</strong> High gas content creates violent eruptions (like Mount St. Helens)</li>
      <li><strong>Effusive:</strong> Low gas content creates flowing lava (like Hawaiian volcanoes)</li>
      <li><strong>Mixed:</strong> Combination of explosive and flowing activity</li>
    </ul>
  </div>

  <div class="volcano-types">
    <h4 class="highlight-text">Types of Real Volcanoes:</h4>
    <div class="three-col">
      <div>
        <strong>Shield Volcanoes:</strong>
        <p>Broad, gently sloping sides built by fluid lava flows. Example: Mauna Loa, Hawaii</p>
      </div>
      <div>
        <strong>Stratovolcanoes:</strong>
        <p>Steep-sided, cone-shaped, built by explosive eruptions. Example: Mount Fuji, Japan</p>
      </div>
      <div>
        <strong>Cinder Cones:</strong>
        <p>Small, steep-sided cones built by gas-charged lava fountains. Example: Parícutin, Mexico</p>
      </div>
    </div>
  </div>

  <h3 class="section-title">Experiment Variations & Extensions:</h3>
  
  <div class="experiment-variations">
    <h4 class="highlight-text">Try These Scientific Variations:</h4>
    
    <p><strong>1. Temperature Effects:</strong> Try warm vs. cold vinegar - does temperature affect eruption intensity?</p>
    
    <p><strong>2. Concentration Studies:</strong> Use different amounts of baking soda (1 tbsp vs. 3 tbsp) and measure eruption height.</p>
    
    <p><strong>3. Additive Effects:</strong> Add different amounts of dish soap to see how it affects foam production.</p>
    
    <p><strong>4. pH Testing:</strong> Use pH strips to test the solution before and after reaction.</p>
    
    <p><strong>5. Timing Experiments:</strong> Measure how long the reaction lasts with different ingredient ratios.</p>
    
    <p><strong>6. Viscosity Simulation:</strong> Add corn syrup to simulate different lava viscosities.</p>
  </div>

  <div class="safety-box">
    <h4 class="red-text">Safety Guidelines:</h4>
    <ul>
      <li><strong>Adult Supervision:</strong> Required for all volcano activities, especially during eruption</li>
      <li><strong>Eye Protection:</strong> Wear safety glasses when conducting eruptions</li>
      <li><strong>Workspace Preparation:</strong> Cover work area with newspaper or plastic sheeting</li>
      <li><strong>Ventilation:</strong> Conduct eruptions in well-ventilated area or outdoors</li>
      <li><strong>Clean-Up:</strong> The reaction produces a safe salt water solution, easy to clean</li>
      <li><strong>Skin Contact:</strong> If solution gets on skin, rinse with water (it's non-toxic)</li>
    </ul>
  </div>

  <h3 class="section-title">Educational Extensions & Research Opportunities:</h3>
  
  <div class="info-box">
    <p><strong>Science Fair Project Ideas:</strong></p>
    <ul>
      <li><strong>Comparative Study:</strong> Test different acids (citric acid, lemon juice) with baking soda</li>
      <li><strong>Geological Research:</strong> Study famous volcanic eruptions throughout history</li>
      <li><strong>Environmental Impact:</strong> Research how volcanic ash affects climate and agriculture</li>
      <li><strong>Prediction Methods:</strong> Learn about modern volcano monitoring technology</li>
      <li><strong>Cultural Studies:</strong> Explore how different cultures view volcanoes in mythology</li>
      <li><strong>Engineering Challenge:</strong> Design structures that can withstand volcanic hazards</li>
    </ul>
  </div>

  <h3 class="section-title">Famous Volcanoes Around the World:</h3>
  
  <div class="geological-box">
    <p><strong>Notable Volcanic Events:</strong></p>
    <ul>
      <li><strong>Mount Vesuvius (79 AD):</strong> Buried Pompeii and Herculaneum, preserving ancient Roman life</li>
      <li><strong>Krakatoa (1883):</strong> Explosive eruption heard 3,000 miles away, affected global climate</li>
      <li><strong>Mount St. Helens (1980):</strong> Lateral blast removed entire north face of mountain</li>
      <li><strong>Mount Pinatubo (1991):</strong> Cooled global temperatures by 0.5°C for two years</li>
      <li><strong>Kilauea (ongoing):</strong> One of world's most active volcanoes, creates new Hawaiian land</li>
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

<!-- MiniScience / Miniscience category embed widget (no products - DIY project) -->
<section class="related-projects">
  <h3>Related Science Categories</h3>
  
  <!-- Related Categories Section -->
  <div class="related-grid">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760562040903106" class="abantecart_category" data-category-id="106" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760562050579139" class="abantecart_category" data-category-id="139" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176056197595675" class="abantecart_category" data-category-id="75" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
        
      </ul>
    </div>
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760561975956127" class="abantecart_category" data-category-id="127" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
        
      </ul>
    </div>
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
       <li id="abc_1760561975956126" class="abantecart_category" data-category-id="126" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
        
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760562097093103" class="abantecart_category" data-category-id="103" data-language="en" data-currency="USD">
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
    <summary>What materials do I need to make a volcano at home?</summary>
    <p>You need common household items: an empty plastic bottle, baking soda, white vinegar, food coloring, dish soap, modeling clay or paper mache, paint, and a base. Most families already have these materials in their kitchen and craft supplies.</p>
  </details>
  <details>
    <summary>Is the volcano eruption safe for kids?</summary>
    <p>Yes! The baking soda and vinegar reaction is completely safe and non-toxic. The reaction produces carbon dioxide gas, water, and sodium acetate (a harmless salt). Adult supervision is recommended primarily for mess management and to ensure proper technique.</p>
  </details>
  <details>
    <summary>How does the baking soda and vinegar reaction work?</summary>
    <p>When baking soda (sodium bicarbonate) mixes with vinegar (acetic acid), it creates a chemical reaction that produces carbon dioxide gas. This gas creates pressure and bubbles, simulating the gas expansion that causes real volcanic eruptions.</p>
  </details>
  <details>
    <summary>Can I make multiple eruptions with the same volcano?</summary>
    <p>Absolutely! Once your volcano structure is built, you can use it for many eruptions. Simply clean out the bottle between eruptions and add fresh baking soda and vinegar mixture. This makes it perfect for experimenting with different formulations.</p>
  </details>
  <details>
    <summary>What can I do to make more realistic lava?</summary>
    <p>Add red and orange food coloring for color, liquid dish soap for foamy texture, and a small amount of corn syrup to thicken the "lava." You can also experiment with different ratios to create fast-flowing or slow-flowing lava effects.</p>
  </details>
  <details>
    <summary>How is this similar to real volcanic eruptions?</summary>
    <p>Both involve pressure buildup and gas expansion. In real volcanoes, dissolved gases in magma expand as pressure decreases, causing explosive eruptions. Our model uses CO2 gas from the chemical reaction to simulate this pressure and gas expansion effect.</p>
  </details>
  <details>
    <summary>What makes this a good science fair project?</summary>
    <p>You can test variables like temperature effects, concentration ratios, different acids, timing measurements, and pH changes. The project combines chemistry (acid-base reactions), geology (volcano types), and engineering (model construction) into one comprehensive study.</p>
  </details>
  <details>
    <summary>Are there different types of eruptions I can simulate?</summary>
    <p>Yes! Use less soap and thicker mixture for effusive (flowing) eruptions like Hawaiian volcanoes, or more soap and thinner mixture for explosive eruptions like stratovolcanoes. You can model different volcanic behaviors with ingredient adjustments.</p>
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