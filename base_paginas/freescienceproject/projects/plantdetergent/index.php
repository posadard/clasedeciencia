<?php
// Project: Plant Growth and Detergent Effects
$project_title = "Plant Growth and Detergent Effects - Do Detergents Affect Plant Growth?";
$project_description = "Investigate how household detergents affect plant growth and health. Learn about plant biology, environmental toxins, and chemical effects on living organisms through controlled experimentation.";
$project_keywords = "plant growth, detergent effects, plant biology, environmental toxins, botanical experiment, chemical effects on plants, pollution impact";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Biology, Environmental Science";
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
    .environmental-section { background: #e6ffe6; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
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
    <p>The <strong class="highlight-text">Plant Growth and Detergent Effects Experiment</strong> investigates how common household cleaning products affect plant health and development. This important environmental science experiment helps students understand the impact of chemical pollutants on living organisms.</p>
    
    <p>Perfect for exploring <strong>plant biology, environmental toxicology, and chemical effects</strong> on living systems, this experiment provides clear, observable results that demonstrate important ecological principles.</p>
  </div>

  <div class="problem-section">
    <h3 class="section-title">🔬 Research Question</h3>
    <p class="highlight-text" style="font-size: 1.1rem;">Do detergents affect plant growth and survival?</p>
    
    <p>This experiment tests whether common household detergents have harmful effects on plant health, growth rate, and overall survival when plants are exposed to detergent solutions over time.</p>
  </div>

  <div class="research-section">
    <h3 class="section-title">🧠 Background Research</h3>
    <p>Plants grow by using <strong>photosynthesis</strong> - converting sunlight, water, and carbon dioxide into food and energy. However, plants can be affected by various chemicals in their environment.</p>
    
    <p><strong>Detergents contain chemicals that may affect plants by:</strong></p>
    <ul>
      <li><strong>Disrupting cell membranes</strong> - Surfactants can damage plant cell walls</li>
      <li><strong>Altering soil pH</strong> - Most detergents are alkaline, changing soil chemistry</li>
      <li><strong>Blocking nutrient uptake</strong> - Chemicals may interfere with root absorption</li>
      <li><strong>Causing cellular toxicity</strong> - Some detergent ingredients are toxic to living cells</li>
      <li><strong>Affecting soil microorganisms</strong> - Beneficial bacteria and fungi may be harmed</li>
    </ul>
    
    <p>Understanding how common household chemicals affect plant life helps us make better environmental choices and understand pollution impacts.</p>
  </div>

  <div class="hypothesis-section">
    <h3 class="section-title">💭 Hypothesis</h3>
    <p><strong>Prediction:</strong> Detergents will negatively affect plant growth and may kill plants because detergent chemicals are designed to break down organic materials and are potentially toxic to most living organisms.</p>
    
    <p><strong>Expected outcome:</strong> Plants watered with detergent solutions will show signs of stress, reduced growth, or death compared to the control plant watered with plain water.</p>
  </div>

  <div class="materials-section">
    <h3 class="section-title">🛠️ Materials Needed</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Living Materials:</h4>
        <ul>
          <li>4 identical small plants in pots (same species, size, and age)</li>
          <li>Potting soil (if repotting needed)</li>
        </ul>
        
        <h4>Test Solutions:</h4>
        <ul>
          <li>3 different types of liquid detergent</li>
          <li>Measuring cups</li>
          <li>Water for dilution and control</li>
        </ul>
      </div>
      <div>
        <h4>Equipment:</h4>
        <ul>
          <li>4 plant labels or markers</li>
          <li>Ruler for measuring growth</li>
          <li>Camera (optional, for documentation)</li>
          <li>Data recording sheet</li>
          <li>Area with adequate sunlight</li>
          <li>Watering containers</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="procedure-section">
    <h3 class="section-title">⚗️ Experimental Procedure</h3>
    
    <ol>
      <li><strong>Prepare plants:</strong> Ensure all 4 plants are the same species, similar size, healthy, and not wilted. Label them as "Control," "Detergent A," "Detergent B," and "Detergent C."</li>
      
      <li><strong>Set up experimental area:</strong> Place all plants in the same location with equal access to sunlight and similar environmental conditions.</li>
      
      <li><strong>Prepare solutions:</strong> Mix each detergent with water in a 1:1 ratio (½ cup detergent + ½ cup water). Prepare plain water for the control plant.</li>
      
      <li><strong>Initial measurements:</strong> Record the initial height, number of leaves, and overall health condition of each plant.</li>
      
      <li><strong>Daily treatment:</strong> Water each plant with its designated solution (control gets plain water, others get their specific detergent mixture).</li>
      
      <li><strong>Daily observations:</strong> Record plant condition, measure growth, count leaves, and note any changes in color, texture, or health.</li>
      
      <li><strong>Duration:</strong> Continue the experiment for 7-10 days, making daily observations and measurements.</li>
      
      <li><strong>Final analysis:</strong> Compare all plants and analyze which treatments caused the most significant effects.</li>
    </ol>
  </div>

  <div class="results-section">
    <h3 class="section-title">📊 Expected Results & Analysis</h3>
    
    <p><strong>Typical experimental outcomes:</strong></p>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>Plant Treatment</th>
          <th>Day 3 Condition</th>
          <th>Day 7 Condition</th>
          <th>Final Outcome</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Control (Water Only)</strong></td>
          <td>Healthy, normal growth</td>
          <td>Continued growth</td>
          <td>✅ Healthy and thriving</td>
        </tr>
        <tr>
          <td><strong>Detergent A</strong></td>
          <td>Leaves yellowing</td>
          <td>Severe wilting</td>
          <td>❌ Dead or dying</td>
        </tr>
        <tr>
          <td><strong>Detergent B</strong></td>
          <td>Stunted growth</td>
          <td>Brown leaf edges</td>
          <td>❌ Severely damaged</td>
        </tr>
        <tr>
          <td><strong>Detergent C</strong></td>
          <td>Leaf discoloration</td>
          <td>Wilting, dropping leaves</td>
          <td>❌ Dead or dying</td>
        </tr>
      </tbody>
    </table>
    
    <p><strong>Analysis:</strong> Results typically confirm the hypothesis - <span class="highlight-text">detergents are toxic to plants</span>. The control plant remains healthy while detergent-treated plants show stress, damage, or death. This demonstrates how household chemicals can harm living organisms and pollute the environment.</p>
  </div>

  <div class="safety-section">
    <h3 class="section-title" style="color: #cc0000;">⚠️ Safety Information</h3>
    <ul>
      <li><strong>Adult supervision required</strong> for handling detergents</li>
      <li><strong>Wash hands thoroughly</strong> after handling detergent solutions</li>
      <li><strong>Avoid skin contact</strong> with concentrated detergents</li>
      <li><strong>Do not inhale</strong> detergent fumes - work in well-ventilated area</li>
      <li><strong>Keep detergents away from eyes</strong> and mouth</li>
      <li><strong>Dispose of solutions properly</strong> - don't pour into storm drains</li>
      <li><strong>Label all containers clearly</strong> to avoid accidental ingestion</li>
    </ul>
  </div>

  <div class="environmental-section">
    <h3 class="section-title">🌍 Environmental Connections</h3>
    <p><strong>Real-world applications of this experiment:</strong></p>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Environmental Impact:</h4>
        <ul>
          <li>Understanding how detergents affect water ecosystems</li>
          <li>Learning about biodegradable vs. non-biodegradable products</li>
          <li>Exploring pollution effects on plant communities</li>
          <li>Investigating runoff impacts on natural habitats</li>
        </ul>
      </div>
      <div>
        <h4>Sustainable Choices:</h4>
        <ul>
          <li>Choosing eco-friendly cleaning products</li>
          <li>Proper disposal of household chemicals</li>
          <li>Understanding product labeling and ingredients</li>
          <li>Reducing chemical use in daily life</li>
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
          <li>Test different detergent concentrations</li>
          <li>Compare eco-friendly vs. conventional detergents</li>
          <li>Test other household chemicals (soap, bleach, etc.)</li>
          <li>Investigate recovery after treatment stops</li>
          <li>Study effects on different plant species</li>
        </ul>
      </div>
      <div>
        <h4>Science Fair Enhancements:</h4>
        <ul>
          <li>Create detailed photo documentation</li>
          <li>Graph growth measurements over time</li>
          <li>Research specific detergent ingredients</li>
          <li>Study water pollution and ecosystem health</li>
          <li>Investigate biodegradable alternatives</li>
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
        <div id="abc_718" class="abantecart_product" data-product-id="2396" data-language="en" data-currency="USD">
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
        <div id="abc_680" class="abantecart_product" data-product-id="2392" data-language="en" data-currency="USD">
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
    <summary>Why do detergents harm plants?</summary>
    <p>Detergents contain surfactants and chemicals designed to break down organic materials. These substances can damage plant cell membranes, disrupt nutrient uptake, alter soil pH, and create toxic conditions that interfere with normal plant functions like photosynthesis and growth.</p>
  </details>
  <details>
    <summary>Are some detergents safer for plants than others?</summary>
    <p>Yes! Biodegradable, plant-based detergents typically cause less harm than conventional chemical detergents. However, even "eco-friendly" products can be harmful in high concentrations. The safest approach is to prevent detergent contact with plants entirely.</p>
  </details>
  <details>
    <summary>What happens to plants in areas with detergent pollution?</summary>
    <p>In nature, detergent pollution from household runoff can cause similar effects: stunted growth, leaf damage, reduced reproduction, and in severe cases, plant death. This disrupts entire ecosystems by affecting food sources for animals and altering plant communities.</p>
  </details>
  <details>
    <summary>Can I use this experiment for a science fair?</summary>
    <p>Absolutely! This experiment provides excellent data for graphs, demonstrates important environmental concepts, and connects to real-world issues. Consider adding measurements, photography, testing different concentrations, or investigating recovery rates for advanced projects.</p>
  </details>
  <details>
    <summary>How does this relate to water pollution?</summary>
    <p>This experiment models what happens when detergents enter natural water systems through household runoff. Understanding these effects helps explain why proper disposal of household chemicals is important for protecting rivers, lakes, and groundwater that support plant and animal life.</p>
  </details>
  <details>
    <summary>What if my results are different than expected?</summary>
    <p>Different plant species may respond differently to detergents, and various detergent formulations have different toxicity levels. Document your actual results carefully - unexpected outcomes are valuable scientific data that can lead to additional research questions.</p>
  </details>
  <details>
    <summary>How can I make this experiment more quantitative?</summary>
    <p>Add precise measurements: daily height measurements, leaf counts, root length (if possible), pH testing of soil, and photographic documentation. Create graphs showing changes over time and calculate percentage differences between treatments.</p>
  </details>
  <details>
    <summary>What should I do with the plants after the experiment?</summary>
    <p>The control plant can continue growing normally. Detergent-treated plants should be disposed of safely since they may contain harmful chemical residues. Don't compost them or place them where they might affect other plants or soil.</p>
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