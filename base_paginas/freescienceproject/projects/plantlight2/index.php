<?php
// Project: Light Intensity and Plant Growth
$project_title = "Light Intensity and Plant Growth - Is Plant Growth Affected by the Amount of Light Received?";
$project_description = "Investigate how different light intensities affect plant growth rates. Learn about photosynthesis efficiency, light saturation points, and optimal growing conditions through controlled experimentation.";
$project_keywords = "light intensity, plant growth, photosynthesis efficiency, light saturation, plant biology, light meters, controlled experiment";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Biology, Plant Science";
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
    .data-table th, .data-table td { border: 1px solid #ddd; padding: 0.75rem; text-align: center; }
    .data-table th { background: #2c5aa0; color: white; font-weight: bold; }
    .data-table tr:nth-child(even) { background: #f8f9fa; }
    .data-table td:first-child { text-align: left; font-weight: bold; }
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
    <p>The <strong class="highlight-text">Light Intensity and Plant Growth Experiment</strong> investigates how different amounts of light affect plant growth rates and health. This advanced plant biology experiment demonstrates photosynthesis efficiency and helps students understand optimal growing conditions.</p>
    
    <p>Perfect for exploring <strong>photosynthesis efficiency, light saturation points, and plant optimization</strong>, this controlled experiment provides quantitative data about how light intensity affects plant development.</p>
  </div>

  <div class="problem-section">
    <h3 class="section-title">🔬 Research Question</h3>
    <p class="highlight-text" style="font-size: 1.1rem;">Is plant growth affected by the amount of light received?</p>
    
    <p>This experiment tests whether increasing light intensity always improves plant growth, or if there's an optimal light level beyond which additional light provides no benefit or may even harm plants.</p>
  </div>

  <div class="research-section">
    <h3 class="section-title">🧠 Background Research</h3>
    <p>Plants use <strong>photosynthesis</strong> to convert light energy into chemical energy (food). However, the relationship between light intensity and photosynthesis is not simply linear - there are optimal ranges and saturation points.</p>
    
    <p><strong>Light intensity and photosynthesis concepts:</strong></p>
    <ul>
      <li><strong>Light Saturation Point</strong> - Maximum light intensity that increases photosynthesis</li>
      <li><strong>Light Compensation Point</strong> - Minimum light needed for survival</li>
      <li><strong>Photoinhibition</strong> - Damage from excessive light intensity</li>
      <li><strong>Heat Stress</strong> - High-intensity lights can damage plants with heat</li>
      <li><strong>Energy Efficiency</strong> - Plants have optimal light ranges for energy use</li>
    </ul>
    
    <p><strong>Factors affecting light response:</strong></p>
    <ul>
      <li><strong>Plant species</strong> - Different plants have different light requirements</li>
      <li><strong>Light spectrum</strong> - Not just intensity, but color composition matters</li>
      <li><strong>Duration</strong> - Total daily light exposure affects growth</li>
      <li><strong>Temperature</strong> - High-intensity lights generate heat</li>
      <li><strong>CO₂ availability</strong> - Limits photosynthesis rate regardless of light</li>
    </ul>
    
    <p>Understanding these principles helps explain why professional growers use specific lighting systems for optimal plant production.</p>
  </div>

  <div class="hypothesis-section">
    <h3 class="section-title">💭 Hypothesis</h3>
    <p><strong>Prediction:</strong> More light will bring more growth up to a certain point, but there will be a limit where additional light doesn't help the plant grow anymore. Very high light intensity might even harm the plant due to heat stress or photoinhibition.</p>
    
    <p><strong>Expected outcome:</strong> The medium-intensity light (100W) should produce the best growth, while low light (25W) may be insufficient and high light (200W) may cause stress or damage from excessive heat.</p>
  </div>

  <div class="materials-section">
    <h3 class="section-title">🛠️ Materials Needed</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Living Materials:</h4>
        <ul>
          <li>3 identical plants (same species, size, and health)</li>
          <li>3 similar-sized pots with saucers</li>
          <li>Potting soil (if repotting needed)</li>
        </ul>
        
        <h4>Test Chambers:</h4>
        <ul>
          <li>3 boxes or cabinets (as controlled growing chambers)</li>
          <li>Ventilation fans or air holes</li>
          <li>Reflective material (optional, aluminum foil)</li>
        </ul>
      </div>
      <div>
        <h4>Lighting Equipment:</h4>
        <ul>
          <li>25-watt light bulb with fixture</li>
          <li>100-watt light bulb with fixture</li>
          <li>200-watt light bulb with fixture</li>
          <li>Extension cords and timers</li>
          <li>Thermometers (to monitor heat)</li>
        </ul>
        
        <h4>Measurement Tools:</h4>
        <ul>
          <li>Ruler for measuring plant height</li>
          <li>Plant labels (25W, 100W, 200W)</li>
          <li>Data recording sheets</li>
          <li>Camera (optional, for documentation)</li>
          <li>Light meter (optional, for precise measurements)</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="procedure-section">
    <h3 class="section-title">⚗️ Experimental Procedure</h3>
    
    <ol>
      <li><strong>Plant preparation:</strong> Select 3 identical plants of the same species, size, and health condition. Label them "25W," "100W," and "200W" to match their light treatments.</li>
      
      <li><strong>Chamber setup:</strong> Prepare 3 identical boxes or cabinets as growing chambers. Ensure each has adequate ventilation to prevent overheating.</li>
      
      <li><strong>Light installation:</strong> Mount one light bulb in each chamber:
        <ul>
          <li>Chamber 1: 25-watt bulb</li>
          <li>Chamber 2: 100-watt bulb</li>
          <li>Chamber 3: 200-watt bulb</li>
        </ul>
      </li>
      
      <li><strong>Safety positioning:</strong> Leave sufficient distance between lights and plants to avoid heat damage. Test temperature with thermometers before adding plants.</li>
      
      <li><strong>Initial measurements:</strong> Record starting height, number of leaves, stem thickness, and overall health condition of each plant.</li>
      
      <li><strong>Daily routine:</strong> 
        <ul>
          <li>Turn lights on for 12-14 hours daily (use timers for consistency)</li>
          <li>Water plants equally when soil feels dry</li>
          <li>Record daily observations using a 1-10 health scale</li>
          <li>Measure growth and count new leaves</li>
          <li>Monitor temperature in each chamber</li>
        </ul>
      </li>
      
      <li><strong>Duration:</strong> Continue the experiment for 10-14 days, making daily observations and measurements.</li>
      
      <li><strong>Final analysis:</strong> Compare all measurements and determine which light intensity produced optimal growth.</li>
    </ol>
  </div>

  <div class="results-section">
    <h3 class="section-title">📊 Expected Results & Analysis</h3>
    
    <p><strong>Sample data recording table (Health Scale 1-10, where 10 = excellent):</strong></p>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>Day</th>
          <th>25 Watt</th>
          <th>100 Watt</th>
          <th>200 Watt</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Day 1</td>
          <td>8</td>
          <td>8</td>
          <td>8</td>
        </tr>
        <tr>
          <td>Day 3</td>
          <td>7</td>
          <td>9</td>
          <td>8</td>
        </tr>
        <tr>
          <td>Day 5</td>
          <td>6</td>
          <td>9</td>
          <td>7</td>
        </tr>
        <tr>
          <td>Day 7</td>
          <td>5</td>
          <td>10</td>
          <td>6</td>
        </tr>
        <tr>
          <td>Day 10</td>
          <td>4</td>
          <td>10</td>
          <td>5</td>
        </tr>
      </tbody>
    </table>
    
    <p><strong>Typical results summary:</strong></p>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>Light Intensity</th>
          <th>Growth Rate</th>
          <th>Leaf Color</th>
          <th>Overall Health</th>
          <th>Issues Observed</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>25W (Low Light)</strong></td>
          <td>Slow growth</td>
          <td>Pale green</td>
          <td>Poor</td>
          <td>Stretching, weak stems</td>
        </tr>
        <tr>
          <td><strong>100W (Medium Light)</strong></td>
          <td>Excellent growth</td>
          <td>Deep green</td>
          <td>Excellent</td>
          <td>None - optimal conditions</td>
        </tr>
        <tr>
          <td><strong>200W (High Light)</strong></td>
          <td>Stunted growth</td>
          <td>Yellow/brown edges</td>
          <td>Stressed</td>
          <td>Heat damage, leaf burn</td>
        </tr>
      </tbody>
    </table>
    
    <p><strong>Analysis:</strong> Results typically show a <span class="highlight-text">"goldilocks effect"</span> - the medium light intensity produces the best growth. Low light limits photosynthesis, while high light causes heat stress and photoinhibition. This demonstrates that plants have optimal light ranges rather than simply "more is better."</p>
  </div>

  <div class="safety-section">
    <h3 class="section-title" style="color: #cc0000;">⚠️ Safety Information</h3>
    <ul>
      <li><strong>Adult supervision required</strong> for electrical setup and high-wattage bulbs</li>
      <li><strong>Fire safety</strong> - ensure adequate ventilation and heat management</li>
      <li><strong>Electrical safety</strong> - keep water away from electrical components</li>
      <li><strong>Heat monitoring</strong> - check chamber temperatures regularly</li>
      <li><strong>Ventilation</strong> - ensure chambers don't overheat plants</li>
      <li><strong>Stable mounting</strong> - secure all light fixtures properly</li>
      <li><strong>Emergency planning</strong> - know how to quickly turn off all lights</li>
      <li><strong>Regular inspection</strong> - check for overheating or electrical issues daily</li>
    </ul>
  </div>

  <div class="science-section">
    <h3 class="section-title">🌱 Photosynthesis Efficiency Science</h3>
    <p><strong>Understanding the biological mechanisms behind the results:</strong></p>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Light-Limited Growth (25W):</h4>
        <ul>
          <li>Insufficient light for optimal photosynthesis</li>
          <li>Plants stretch (etiolation) seeking more light</li>
          <li>Pale color from reduced chlorophyll</li>
          <li>Weak stems from rapid elongation</li>
        </ul>
      </div>
      <div>
        <h4>Light-Saturated Growth (200W):</h4>
        <ul>
          <li>Photosystem damage from excess energy</li>
          <li>Heat stress from high-wattage bulbs</li>
          <li>Leaf burn and cellular damage</li>
          <li>Energy waste - plant can't use all light</li>
        </ul>
      </div>
    </div>
    
    <p><strong>Optimal Light Zone (100W):</strong> Plants receive enough light for maximum photosynthesis without damage, achieving the perfect balance between energy input and plant capacity.</p>
  </div>

  <div class="extensions-section">
    <h3 class="section-title">🔬 Project Extensions & Variations</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Advanced Investigations:</h4>
        <ul>
          <li>Test different light spectrums (LED colors)</li>
          <li>Measure actual light intensity with light meters</li>
          <li>Monitor chamber temperatures precisely</li>
          <li>Test different plant species</li>
          <li>Vary light duration (photoperiod effects)</li>
        </ul>
      </div>
      <div>
        <h4>Science Fair Enhancements:</h4>
        <ul>
          <li>Create detailed growth charts and graphs</li>
          <li>Calculate photosynthesis rates</li>
          <li>Research commercial growing operations</li>
          <li>Study LED vs. traditional lighting efficiency</li>
          <li>Investigate seasonal light changes in nature</li>
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
    <summary>Why doesn't more light always mean better plant growth?</summary>
    <p>Plants have a "light saturation point" where they can't use additional light energy effectively. Beyond this point, excess light can cause heat stress, photoinhibition (damage to photosystems), and cellular damage rather than improved growth.</p>
  </details>
  <details>
    <summary>What happens to plants that get too little light?</summary>
    <p>Plants with insufficient light show "etiolation" - they become pale, stretch toward light sources, develop weak stems, and have reduced photosynthesis. They're essentially starving for the energy they need to grow properly.</p>
  </details>
  <details>
    <summary>How do I know if my plants are getting too much light?</summary>
    <p>Signs of too much light include leaf burn (brown or yellow edges), stunted growth, wilting despite adequate water, and excessive heat in the growing area. The plants may also appear stressed or bleached.</p>
  </details>
  <details>
    <summary>What's the best wattage for growing plants indoors?</summary>
    <p>It depends on the plant species, but for most houseplants, 20-40 watts per square foot of growing area works well. LED grow lights are more efficient than traditional bulbs and produce less heat.</p>
  </details>
  <details>
    <summary>How does this experiment relate to commercial growing?</summary>
    <p>Commercial growers use this same principle to optimize plant production. They carefully calculate light intensity, duration, and spectrum to maximize growth while minimizing energy costs and plant stress.</p>
  </details>
  <details>
    <summary>Can I use different colored lights for this experiment?</summary>
    <p>Yes! Testing red, blue, and white LED lights can show how different light spectrums affect growth. Plants use red light for flowering and blue light for leaf growth most efficiently.</p>
  </details>
  <details>
    <summary>Why do my high-wattage chambers get so hot?</summary>
    <p>Traditional incandescent bulbs convert most energy to heat rather than light. This is why modern growers use LED lights - they produce the same light intensity with much less heat generation.</p>
  </details>
  <details>
    <summary>How long should I run the lights each day?</summary>
    <p>Most plants need 12-16 hours of light daily for optimal growth. You can use timers to maintain consistent photoperiods. Some plants also need dark periods for proper development.</p>
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