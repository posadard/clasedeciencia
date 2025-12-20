<?php
// Project: Plants and Light Sources Comparison
$project_title = "Plants and Light Sources - Do Plants Grow Better with Sunlight or Artificial Light?";
$project_description = "Compare plant growth under natural sunlight versus artificial light sources. Learn about photosynthesis, light spectrum, and plant biology through controlled experimentation.";
$project_keywords = "plant growth, sunlight vs artificial light, photosynthesis, light spectrum, plant biology, botanical experiment, indoor gardening";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Biology, Plant Science";
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
    .hypothesis-section { background: #f0f8ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .materials-section { background: #fff8f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .procedure-section { background: #f8fff8; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .results-section { background: #fff6f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .safety-section { background: #ffe6e6; border: 1px solid #ff4444; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .extensions-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .science-section { background: #e6ffe6; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #2c5aa0; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #2c5aa0; font-weight: bold; }
    .center { text-align: center; }
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
    }
    /* Data table styling */
    .data-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    .data-table th, .data-table td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; }
    .data-table th { background: #2c5aa0; color: white; font-weight: bold; }
    .data-table tr:nth-child(even) { background: #f8f9fa; }
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
    <p>The <strong class="highlight-text">Plants and Light Sources Experiment</strong> compares how plants grow under natural sunlight versus artificial light. This fundamental plant biology experiment helps students understand photosynthesis, light requirements, and indoor growing techniques.</p>
    
    <p>Perfect for exploring <strong>plant biology, photosynthesis, and light spectrum science</strong>, this controlled experiment provides clear observable results about how different light sources affect plant health and growth.</p>
  </div>

  <div class="problem-section">
    <h3 class="section-title">🔬 Research Question</h3>
    <p class="highlight-text" style="font-size: 1.1rem;">Do plants grow better with sunlight or artificial light?</p>
    
    <p>This experiment tests whether plants can grow as well under artificial lighting as they do under natural sunlight, and which light source produces healthier, stronger plants.</p>
  </div>

  <div class="research-section">
    <h3 class="section-title">🧠 Background Research</h3>
    <p>Plants use <strong>photosynthesis</strong> to convert light energy into chemical energy (food). The process requires light, carbon dioxide, and water to produce glucose and oxygen.</p>
    
    <p><strong>Light and photosynthesis fundamentals:</strong></p>
    <ul>
      <li><strong>Chlorophyll</strong> - Green pigment that captures light energy</li>
      <li><strong>Light spectrum</strong> - Plants use primarily red and blue wavelengths</li>
      <li><strong>Light intensity</strong> - Brighter light generally means more photosynthesis</li>
      <li><strong>Duration</strong> - Most plants need 12-16 hours of light daily</li>
      <li><strong>Quality</strong> - Full spectrum light contains all wavelengths plants need</li>
    </ul>
    
    <p><strong>Natural sunlight vs. artificial light:</strong></p>
    <ul>
      <li><strong>Sunlight</strong> - Full spectrum, variable intensity, free but weather-dependent</li>
      <li><strong>Artificial light</strong> - Controlled spectrum and intensity, consistent but uses energy</li>
      <li><strong>Indoor growing</strong> - Allows year-round cultivation regardless of season</li>
      <li><strong>Spectrum differences</strong> - Some artificial lights lack certain wavelengths</li>
    </ul>
    
    <p>Understanding light requirements helps explain how indoor gardening and greenhouse growing work.</p>
  </div>

  <div class="hypothesis-section">
    <h3 class="section-title">💭 Hypothesis</h3>
    <p><strong>Prediction:</strong> Plants will grow better under natural sunlight because sunlight provides the full spectrum of light that plants evolved to use, while most artificial lights may lack certain wavelengths or adequate intensity.</p>
    
    <p><strong>Expected outcome:</strong> The sunlight plant should show better growth, greener color, and stronger stems compared to the artificial light plant, though both should survive if given adequate light duration.</p>
  </div>

  <div class="materials-section">
    <h3 class="section-title">🛠️ Materials Needed</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Living Materials:</h4>
        <ul>
          <li>2 identical plants (same species, size, and health)</li>
          <li>2 similar-sized pots with saucers</li>
          <li>Potting soil (if repotting needed)</li>
        </ul>
        
        <h4>Lighting Equipment:</h4>
        <ul>
          <li>Desk lamp or grow light</li>
          <li>Timer outlet (optional but recommended)</li>
          <li>Area with good natural sunlight</li>
        </ul>
      </div>
      <div>
        <h4>Measurement Tools:</h4>
        <ul>
          <li>Ruler for measuring plant height</li>
          <li>Plant labels or tags</li>
          <li>Camera (optional, for documentation)</li>
          <li>Data recording sheet</li>
          <li>Watering container</li>
        </ul>
        
        <h4>Optional Advanced Tools:</h4>
        <ul>
          <li>Light meter (measures light intensity)</li>
          <li>Different types of artificial lights to test</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="procedure-section">
    <h3 class="section-title">⚗️ Experimental Procedure</h3>
    
    <ol>
      <li><strong>Plant preparation:</strong> Select 2 identical plants of the same species, size, and health condition. Label one "Sample A - Sunlight" and the other "Sample B - Artificial Light."</li>
      
      <li><strong>Initial measurements:</strong> Record starting height, number of leaves, stem thickness, and overall health condition of both plants. Take photos if possible.</li>
      
      <li><strong>Sunlight setup:</strong> Place Sample A on a windowsill or location where it receives direct sunlight for most of the day (ideally 8+ hours).</li>
      
      <li><strong>Artificial light setup:</strong> Place Sample B under the lamp in a room away from natural light. Position the lamp 12-18 inches above the plant.</li>
      
      <li><strong>Light timing:</strong> Turn the artificial light on at dusk and off at dawn to match the sunlight plant's light duration. Use a timer if available for consistency.</li>
      
      <li><strong>Daily care:</strong> Water both plants equally when soil feels dry. Ensure both receive the same amount of water and care.</li>
      
      <li><strong>Daily observations:</strong> Record daily measurements of height, leaf count, color, and general health for both plants.</li>
      
      <li><strong>Duration:</strong> Continue the experiment for 2-3 weeks, making daily observations and measurements.</li>
      
      <li><strong>Final analysis:</strong> Compare final measurements with starting data and determine which light source produced better growth.</li>
    </ol>
  </div>

  <div class="results-section">
    <h3 class="section-title">📊 Expected Results & Analysis</h3>
    
    <p><strong>Typical experimental outcomes:</strong></p>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>Measurement</th>
          <th>Sample A (Sunlight)</th>
          <th>Sample B (Artificial Light)</th>
          <th>Difference</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Height Growth</strong></td>
          <td>2.5 inches</td>
          <td>1.8 inches</td>
          <td>Sunlight +0.7 inches</td>
        </tr>
        <tr>
          <td><strong>New Leaves</strong></td>
          <td>6 new leaves</td>
          <td>4 new leaves</td>
          <td>Sunlight +2 leaves</td>
        </tr>
        <tr>
          <td><strong>Leaf Color</strong></td>
          <td>Deep green</td>
          <td>Light green/pale</td>
          <td>Sunlight healthier color</td>
        </tr>
        <tr>
          <td><strong>Stem Strength</strong></td>
          <td>Strong, thick</td>
          <td>Thin, may lean</td>
          <td>Sunlight stronger</td>
        </tr>
        <tr>
          <td><strong>Overall Health</strong></td>
          <td>Excellent</td>
          <td>Good but weaker</td>
          <td>Sunlight superior</td>
        </tr>
      </tbody>
    </table>
    
    <p><strong>Analysis:</strong> Results typically show <span class="highlight-text">sunlight producing superior plant growth</span>. Natural sunlight provides full spectrum light with optimal intensity and quality. Artificial light plants often grow more slowly, have paler leaves, and weaker stems, though they can still survive and grow.</p>
    
    <p><strong>Why sunlight usually wins:</strong> Full spectrum light, natural intensity changes throughout the day, and evolutionary adaptation to solar radiation give sunlight-grown plants advantages.</p>
  </div>

  <div class="safety-section">
    <h3 class="section-title" style="color: #cc0000;">⚠️ Safety Information</h3>
    <ul>
      <li><strong>Electrical safety</strong> when using lamps and timers</li>
      <li><strong>Keep water away from electrical outlets</strong> and cords</li>
      <li><strong>Use stable surfaces</strong> for plants to prevent falls</li>
      <li><strong>Position lamps securely</strong> to avoid tipping</li>
      <li><strong>Check that artificial lights don't overheat</strong> plants</li>
      <li><strong>Adult supervision recommended</strong> for electrical setup</li>
      <li><strong>Handle plants gently</strong> during measurements</li>
    </ul>
  </div>

  <div class="science-section">
    <h3 class="section-title">🌱 Photosynthesis and Light Science</h3>
    <p><strong>Understanding the plant biology behind the results:</strong></p>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>How Photosynthesis Works:</h4>
        <ul>
          <li>Chlorophyll captures light energy</li>
          <li>Light energy splits water molecules</li>
          <li>Carbon dioxide combines with hydrogen</li>
          <li>Glucose (plant food) and oxygen are produced</li>
        </ul>
      </div>
      <div>
        <h4>Light Spectrum Importance:</h4>
        <ul>
          <li>Red light (660-700nm) - Flowering and fruiting</li>
          <li>Blue light (400-500nm) - Leaf growth and photosynthesis</li>
          <li>Green light (500-600nm) - Less used, reflected back</li>
          <li>Full spectrum provides complete plant nutrition</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="extensions-section">
    <h3 class="section-title">🔬 Project Extensions & Variations</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Advanced Investigations:</h4>
        <ul>
          <li>Test different types of artificial lights (LED, fluorescent, incandescent)</li>
          <li>Vary the distance between light and plant</li>
          <li>Test different light durations (8, 12, 16 hours)</li>
          <li>Compare different plant species</li>
          <li>Measure light intensity with a light meter</li>
        </ul>
      </div>
      <div>
        <h4>Science Fair Enhancements:</h4>
        <ul>
          <li>Create daily growth charts and graphs</li>
          <li>Take time-lapse photography</li>
          <li>Research indoor farming and hydroponics</li>
          <li>Study seasonal changes in sunlight</li>
          <li>Investigate grow light technology</li>
        </ul>
      </div>
    </div>
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
        <div id="abc_536" class="abantecart_product" data-product-id="957" data-language="en" data-currency="USD">
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
        <div id="abc_558" class="abantecart_product" data-product-id="900" data-language="en" data-currency="USD">
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
        <div id="abc_724" class="abantecart_product" data-product-id="3399" data-language="en" data-currency="USD">
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
        <li id="abc_176054520457687" class="abantecart_category" data-category-id="87" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760545216386106" class="abantecart_category" data-category-id="106" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176054522858971" class="abantecart_category" data-category-id="71" data-language="en" data-currency="USD">
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
    <summary>Why do plants usually grow better in sunlight than artificial light?</summary>
    <p>Natural sunlight provides the full spectrum of light that plants evolved to use, including the optimal balance of red, blue, and other wavelengths. Most artificial lights lack certain wavelengths or don't provide the same intensity as sunlight.</p>
  </details>
  <details>
    <summary>What type of artificial light works best for plants?</summary>
    <p>Full-spectrum LED grow lights work best because they provide the red and blue wavelengths plants need most. Fluorescent lights work moderately well, while incandescent bulbs are generally too hot and don't provide the right spectrum.</p>
  </details>
  <details>
    <summary>How many hours of light do plants need each day?</summary>
    <p>Most plants need 12-16 hours of light daily for optimal growth. Some plants require specific day/night cycles, but for this experiment, matching the natural sunlight duration (about 12 hours) works well.</p>
  </details>
  <details>
    <summary>Can plants survive completely on artificial light?</summary>
    <p>Yes! With the right type of artificial lighting, plants can grow entirely indoors. This is how hydroponics and indoor farming work. The key is using lights that provide the correct spectrum and intensity.</p>
  </details>
  <details>
    <summary>Why might my artificial light plant look pale or weak?</summary>
    <p>Pale, weak growth usually indicates insufficient light intensity or poor light spectrum. The plant may be getting some light but not enough quality light for robust photosynthesis and healthy development.</p>
  </details>
  <details>
    <summary>How far should I place the artificial light from my plant?</summary>
    <p>Generally 12-18 inches for most desk lamps or grow lights. Too close can burn the plant, too far reduces light intensity. LED lights can be closer than fluorescent or incandescent lights since they produce less heat.</p>
  </details>
  <details>
    <summary>What if both my plants grow the same amount?</summary>
    <p>Sometimes artificial lights work very well! Document your results - if both plants grew equally, that's valuable data showing that your artificial light setup was effective. Consider what type of light you used and why it worked.</p>
  </details>
  <details>
    <summary>Can I use this experiment to start an indoor garden?</summary>
    <p>Absolutely! This experiment teaches the basics of indoor growing. If your artificial light setup works well, you can expand it to grow herbs, vegetables, or other plants indoors year-round.</p>
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