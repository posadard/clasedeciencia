<?php
// Project: Advanced Volcano Display - DIY Educational Project
$project_title = "Build Multiple Volcano Display - Advanced DIY Geology Project";
$project_description = "Create an advanced volcano landscape featuring multiple volcano types and learn about famous volcanoes around the world through hands-on geological study and experimentation.";
$project_keywords = "volcano display, multiple volcanoes, volcano landscape, geology project, famous volcanoes, volcanic eruptions, earth science, DIY volcano kit";
$project_grade_level = "Elementary to High School"; // advanced project
$project_subject = "Earth Science, Geology";
$project_difficulty = "Intermediate to Advanced";

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
    .advanced-notice { background: #fff0e6; border: 2px solid #ff6b35; padding: 1rem; border-radius: 8px; margin: 1rem 0; text-align: center; }
    .section-title { color: #990000; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #990000; font-weight: bold; }
    .red-text { color: #ff0000; font-weight: bold; }
    .materials-grid { display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start; margin: 1rem 0; }
    .two-col { display: grid; grid-template-columns: 1fr 320px; gap: 1rem; align-items: start; margin: 1rem 0; }
    .three-col { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin: 1rem 0; }
    .four-col { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; border-radius: 8px; }
    .center { text-align: center; }
    .step-box { background: #f9f9f9; border-left: 4px solid #ff6b35; padding: 1rem; margin: 0.5rem 0; }
    .volcano-gallery { background: #fff5f0; border: 1px solid #ff6b35; padding: 1rem; margin: 1rem 0; }
    .famous-volcanoes { background: #f0f8ff; border: 1px solid #2c5aa0; padding: 1rem; margin: 1rem 0; }
    .experiment-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; margin: 1rem 0; }
    .experiment-card { background: #f9f9f9; border: 1px solid #ddd; padding: 1rem; border-radius: 8px; }
    @media (max-width: 720px) {
      .materials-grid, .two-col, .three-col, .four-col { grid-template-columns: 1fr; }
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

  <div class="advanced-notice">
    <p><strong>Advanced DIY Project:</strong> Create a comprehensive volcano landscape featuring multiple volcano types using household materials. This project builds on basic volcano science to create an impressive educational display!</p>
  </div>

  <div class="intro-section">
    <p><strong class="highlight-text">Build Your Own Volcano Landscape Display!</strong> Create multiple volcanoes representing different geological types and famous volcanoes around the world. This advanced project combines geology, chemistry, geography, and art into one comprehensive educational experience.</p>
  </div>

  <div class="materials-grid">
    <div>
      <img src="volcano2.jpg" alt="Advanced volcano landscape display with multiple volcano types" class="responsive-img" />
      <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;"><strong>Advanced project - Adult supervision recommended</strong></p>
    </div>
    <div>
      <h3 class="highlight-text">Multiple Volcano Types & Display</h3>
      
      <p>This advanced project lets you create a <strong>realistic volcano landscape</strong> featuring different types of volcanoes, each with unique characteristics and eruption patterns.</p>
      
      <p><strong>Educational Goals:</strong> Learn about famous volcanoes worldwide, understand different eruption styles, practice advanced model-making techniques, and conduct comparative studies of volcanic behavior.</p>
      
      <p class="red-text">Perfect for advanced science fair projects, classroom displays, or serious geology enthusiasts!</p>
    </div>
  </div>

  <h3 class="section-title">Materials for Advanced Volcano Landscape:</h3>
  
  <div class="materials-box">
    <h4><strong>Base Construction Materials:</strong></h4>
    <div class="four-col">
      <div>
        <strong>Landscape Base:</strong>
        <ul>
          <li>Large cardboard/plywood base (24" x 18")</li>
          <li>Multiple plastic bottles (various sizes)</li>
          <li>Modeling clay or paper mache</li>
          <li>Foam board (optional)</li>
        </ul>
      </div>
      <div>
        <strong>Painting & Decoration:</strong>
        <ul>
          <li>Acrylic paints (multiple earth tones)</li>
          <li>Fine brushes for detail work</li>
          <li>Textural materials (sand, gravel)</li>
          <li>Sponges for texture painting</li>
        </ul>
      </div>
      <div>
        <strong>Eruption Chemistry:</strong>
        <ul>
          <li>Baking soda (large quantity)</li>
          <li>White vinegar</li>
          <li>Food coloring (red, orange, yellow)</li>
          <li>Liquid dish soap</li>
          <li>Corn syrup (for thick lava)</li>
        </ul>
      </div>
      <div>
        <strong>Advanced Features:</strong>
        <ul>
          <li>LED lights (battery powered)</li>
          <li>Clear tubing for lava flows</li>
          <li>Small mirrors for water features</li>
          <li>Miniature trees/vegetation</li>
        </ul>
      </div>
    </div>
  </div>

  <h3 class="section-title">Design Your Volcano Landscape:</h3>
  
  <div class="volcano-gallery">
    <h4 class="highlight-text">Featured Volcano Types to Build:</h4>
    
    <div class="three-col">
      <div class="experiment-card">
        <h5><strong>1. Shield Volcano (Hawaiian Type)</strong></h5>
        <p><strong>Shape:</strong> Broad, gently sloping</p>
        <p><strong>Eruption:</strong> Effusive, flowing lava</p>
        <p><strong>Recipe:</strong> Less soap, thicker mixture with corn syrup</p>
        <p><strong>Example:</strong> Mauna Loa, Hawaii</p>
      </div>
      
      <div class="experiment-card">
        <h5><strong>2. Stratovolcano (Composite)</strong></h5>
        <p><strong>Shape:</strong> Steep-sided, cone-shaped</p>
        <p><strong>Eruption:</strong> Explosive with ash clouds</p>
        <p><strong>Recipe:</strong> More soap for explosive foam</p>
        <p><strong>Example:</strong> Mount Fuji, Japan</p>
      </div>
      
      <div class="experiment-card">
        <h5><strong>3. Cinder Cone</strong></h5>
        <p><strong>Shape:</strong> Small, steep-sided</p>
        <p><strong>Eruption:</strong> Moderate, lava fountains</p>
        <p><strong>Recipe:</strong> Quick, short bursts</p>
        <p><strong>Example:</strong> Parícutin, Mexico</p>
      </div>
    </div>
  </div>

  <h3 class="section-title">Advanced Construction Steps:</h3>
  
  <div class="step-box">
    <h4><strong>Step 1: Plan Your Landscape Layout</strong></h4>
    <p>Sketch your volcano landscape showing different volcano types, their relative sizes, and additional features like rivers, lakes, or towns. Research real volcanic regions for inspiration (Ring of Fire, Iceland, Italy).</p>
  </div>

  <div class="step-box">
    <h4><strong>Step 2: Build Multiple Volcano Bases</strong></h4>
    <p>Position bottles of different sizes across your base. Use larger bottles for major stratovolcanoes, medium bottles for shield volcanoes, and small bottles for cinder cones. Secure each with clay or strong tape.</p>
  </div>

  <div class="step-box">
    <h4><strong>Step 3: Create Realistic Topography</strong></h4>
    <p>Build up the landscape between volcanoes using clay, paper mache, or foam. Create valleys, ridges, and realistic terrain. Add features like lava tubes, crater lakes, or fumarole fields.</p>
  </div>

  <div class="step-box">
    <h4><strong>Step 4: Advanced Painting & Texturing</strong></h4>
    <p>Use multiple paint colors to create realistic rock formations. Apply base colors, then dry-brush highlights and shadows. Add texture with sand, gravel, or sponge techniques while paint is wet.</p>
  </div>

  <div class="step-box">
    <h4><strong>Step 5: Add Educational Features</strong></h4>
    <p>Create labels for each volcano type, add small flags marking famous volcanoes, include a legend explaining different eruption styles, and position educational information cards around the display.</p>
  </div>

  <div class="center">
    <img src="volcano.jpg" alt="Detailed volcano landscape showing multiple eruption types" class="responsive-img" style="max-width: 600px; margin: 1rem auto;" />
  </div>

  <h3 class="section-title">Famous Volcanoes to Study & Recreate:</h3>
  
  <div class="famous-volcanoes">
    <h4 class="highlight-text">Top 10 Famous Volcanoes for Your Display:</h4>
    
    <div class="three-col">
      <div>
        <strong>Explosive Stratovolcanoes:</strong>
        <ul>
          <li><strong>Mount Vesuvius</strong> - Italy (79 AD eruption)</li>
          <li><strong>Mount St. Helens</strong> - USA (1980 lateral blast)</li>
          <li><strong>Krakatoa</strong> - Indonesia (1883 explosion)</li>
          <li><strong>Mount Fuji</strong> - Japan (perfectly shaped cone)</li>
        </ul>
      </div>
      <div>
        <strong>Shield & Hawaiian Types:</strong>
        <ul>
          <li><strong>Mauna Loa</strong> - Hawaii (world's largest)</li>
          <li><strong>Kilauea</strong> - Hawaii (most active)</li>
          <li><strong>Eyjafjallajökull</strong> - Iceland (2010 ash cloud)</li>
        </ul>
      </div>
      <div>
        <strong>Unique & Dangerous:</strong>
        <ul>
          <li><strong>Mount Pinatubo</strong> - Philippines (1991 climate impact)</li>
          <li><strong>Yellowstone</strong> - USA (supervolcano caldera)</li>
          <li><strong>Santorini</strong> - Greece (Bronze Age civilization impact)</li>
        </ul>
      </div>
    </div>
  </div>

  <h3 class="section-title">Advanced Eruption Experiments:</h3>
  
  <div class="experiment-grid">
    <div class="experiment-card">
      <h5><strong>Experiment 1: Eruption Height Comparison</strong></h5>
      <p><strong>Goal:</strong> Compare eruption heights of different volcano types</p>
      <p><strong>Method:</strong> Use different baking soda concentrations (1-4 tablespoons)</p>
      <p><strong>Measure:</strong> Maximum height reached by foam/liquid</p>
      <p><strong>Record:</strong> Create bar graph of results</p>
    </div>
    
    <div class="experiment-card">
      <h5><strong>Experiment 2: Lava Viscosity Study</strong></h5>
      <p><strong>Goal:</strong> Simulate different lava types</p>
      <p><strong>Method:</strong> Add corn syrup, honey, or flour to change thickness</p>
      <p><strong>Measure:</strong> Flow distance and speed</p>
      <p><strong>Record:</strong> Time how long it takes to flow 12 inches</p>
    </div>
    
    <div class="experiment-card">
      <h5><strong>Experiment 3: Temperature Effects</strong></h5>
      <p><strong>Goal:</strong> Test how temperature affects eruption intensity</p>
      <p><strong>Method:</strong> Use cold, room temp, and warm vinegar</p>
      <p><strong>Measure:</strong> Reaction speed and foam volume</p>
      <p><strong>Record:</strong> Graph temperature vs. eruption characteristics</p>
    </div>
  </div>

  <h3 class="section-title">Research Questions & Extensions:</h3>
  
  <div class="info-box">
    <p><strong>Burning Questions to Investigate:</strong></p>
    <ul>
      <li><strong>Formation:</strong> What causes volcanoes to form in the first place?</li>
      <li><strong>Prediction:</strong> How do scientists predict volcanic eruptions?</li>
      <li><strong>Types:</strong> Why do different volcanoes have different eruption styles?</li>
      <li><strong>History:</strong> What were the most devastating eruptions in human history?</li>
      <li><strong>Climate:</strong> How do major eruptions affect global weather patterns?</li>
      <li><strong>Benefits:</strong> What positive effects do volcanoes have on ecosystems?</li>
      <li><strong>Monitoring:</strong> What modern technology is used to study active volcanoes?</li>
    </ul>
  </div>

  <div class="geological-box">
    <h4 class="highlight-text">Advanced Geological Concepts:</h4>
    <p><strong>Plate Tectonics:</strong> Most volcanoes occur at plate boundaries where Earth's crustal plates meet and interact.</p>
    <p><strong>Magma Composition:</strong> Silica content determines eruption style - high silica = explosive, low silica = effusive.</p>
    <p><strong>Volcanic Hazards:</strong> Lava flows, pyroclastic flows, ash fall, lahars, and volcanic gases all pose different risks.</p>
    <p><strong>Economic Impact:</strong> Volcanoes provide geothermal energy, fertile soils, and tourist attractions, but also pose significant risks to populations.</p>
  </div>

  <div class="safety-box">
    <h4 class="red-text">Safety Guidelines for Advanced Project:</h4>
    <ul>
      <li><strong>Adult Supervision:</strong> Essential for all construction and eruption activities</li>
      <li><strong>Workspace Protection:</strong> Use large plastic sheets to protect surfaces</li>
      <li><strong>Eye Protection:</strong> Safety glasses required during all eruption experiments</li>
      <li><strong>Ventilation:</strong> Ensure good air circulation, especially when using paints</li>
      <li><strong>Chemical Safety:</strong> All materials are non-toxic, but avoid skin/eye contact with mixtures</li>
      <li><strong>Electrical Safety:</strong> If using LED lights, ensure waterproof connections</li>
      <li><strong>Clean-Up:</strong> Have cleaning supplies ready - this project can be messy!</li>
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
    <summary>How is this different from a basic volcano project?</summary>
    <p>This advanced project creates multiple volcanoes representing different geological types (shield, stratovolcano, cinder cone) with realistic landscape features, detailed painting, and comparative eruption experiments. It's designed for serious geology study and impressive displays.</p>
  </details>
  <details>
    <summary>How many volcanoes should I include in my landscape?</summary>
    <p>Start with 3-5 volcanoes of different types and sizes. You can include one major stratovolcano, one shield volcano, 2-3 smaller cinder cones, and possibly a caldera feature. This provides good variety without overcrowding your display.</p>
  </details>
  <details>
    <summary>What makes each volcano type erupt differently?</summary>
    <p>Use different mixture ratios: shield volcanoes get thicker, slower-flowing "lava" with corn syrup; stratovolcanoes get more soap for explosive foam; cinder cones get quick, small bursts. This simulates how real magma composition affects eruption style.</p>
  </details>
  <details>
    <summary>How can I make this into a science fair project?</summary>
    <p>Focus on comparative studies: measure eruption heights, test different lava viscosities, research famous volcano locations, create timeline of major eruptions, or study volcanic impacts on climate. Document everything with photos, graphs, and data tables.</p>
  </details>
  <details>
    <summary>What famous volcanoes should I research and include?</summary>
    <p>Study volcanoes with different characteristics: Vesuvius (historical impact), Mount St. Helens (lateral blast), Kilauea (continuous activity), Krakatoa (explosive power), Yellowstone (supervolcano), and Eyjafjallajökull (modern air travel disruption).</p>
  </details>
  <details>
    <summary>How do I create realistic volcanic terrain and textures?</summary>
    <p>Use multiple paint colors, sponge techniques for texture, add real sand or fine gravel while paint is wet, create shadow and highlight effects with dry brushing, and use reference photos of real volcanic landscapes for inspiration.</p>
  </details>
  <details>
    <summary>Can I add special effects like lights or sound?</summary>
    <p>Yes! Battery-powered LED lights can simulate lava glow (use red/orange LEDs), small speakers can play eruption sounds, and mirrors can create water features. Just ensure all electrical components are safely waterproofed and supervised by adults.</p>
  </details>
  <details>
    <summary>How do I conduct controlled experiments with multiple volcanoes?</summary>
    <p>Test one variable at a time: use identical mixtures in different sized volcanoes, or test different mixture ratios in identical volcano models. Always measure and record results systematically, and repeat experiments for reliable data.</p>
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